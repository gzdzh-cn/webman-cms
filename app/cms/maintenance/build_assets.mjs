#!/usr/bin/env node
/** Verify the locked manifest or reproduce its exact assets; never rewrite its contract. */
import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import crypto from 'node:crypto';
import {createRequire} from 'node:module';
import {execFileSync} from 'node:child_process';
import {fileURLToPath} from 'node:url';

const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../../..');
const publicRoots=['public/admin-assets/'];
const privateRoots=['app/cms/resources/fonts/','app/cms/resources/defaults/','app/cms/resources/legacy-assets/','app/view/cms/legacy-template/'];
const sourceRoots=['public/component/','public/plugins/','public/static/','public/admin/','public/config/','public/errpage/','app/cms/resources/compat-assets/'];
const manifestRelative='app/cms/resources/asset-manifest.json';
const sha=bytes=>crypto.createHash('sha256').update(bytes).digest('hex');
const isSha=value=>typeof value==='string'&&/^[a-f0-9]{64}$/.test(value);
const fail=message=>{throw new Error(message);};
function relative(name){
 if(typeof name!=='string'||!name||path.isAbsolute(name)||/[\\\x00-\x1f\x7f]/.test(name)||name.split('/').some(p=>!p||p.startsWith('.')))fail('Unsafe relative path: '+String(name));
 return name;
}
function noLinks(name){
 if(!path.isAbsolute(name)||name.split(path.sep).includes('..'))fail('Path must be absolute and canonical');
 let current=path.parse(name).root;
 for(const part of name.slice(current.length).split(path.sep).filter(Boolean)){
  current=path.join(current,part);
  if(fs.existsSync(current)||isLink(current)){if(fs.lstatSync(current).isSymbolicLink())fail('Symlink in path: '+current);}
 }
 return name;
}
function isLink(name){try{return fs.lstatSync(name).isSymbolicLink();}catch(e){if(e.code==='ENOENT'||e.code==='ENOTDIR')return false;throw e;}}
function regular(name){noLinks(name);return fs.existsSync(name)&&fs.statSync(name).isFile();}
function expectedFile(name,record){return regular(name)&&fs.statSync(name).size===record.size&&sha(fs.readFileSync(name))===record.sha256;}
function options(){
 const result={apply:false};const args=process.argv.slice(2);
 for(let i=0;i<args.length;i++){
  const name=args[i];if(name==='--apply'){if(result.apply)fail('Duplicate --apply');result.apply=true;continue;}
  if(!['--input','--output'].includes(name)||i+1>=args.length||args[i+1].startsWith('--'))fail('Unknown or incomplete build option: '+name);
  const key=name.slice(2);if(result[key])fail('Duplicate '+name);result[key]=noLinks(args[++i]);
 }
 return result;
}
function loadManifest(){
 const name=noLinks(path.join(root,manifestRelative));const bytes=fs.readFileSync(name);const data=JSON.parse(bytes);
 if(data.format!==2||!Array.isArray(data.files)||!data.files.length)fail('Only nonempty format-2 asset manifests are supported');
 const targets=new Map(),sources=new Map();
 for(const f of data.files){
  relative(f.source);relative(f.target);
  if(!sourceRoots.some(p=>f.source.startsWith(p))&&!['public/favicon.ico','public/avatar.png','public/close.html','public/robots.txt'].includes(f.source))fail('Unexpected source: '+f.source);
  if(typeof f.public!=='boolean'||typeof f.html_allowed!=='boolean'||!Array.isArray(f.aliases))fail('Invalid asset classification: '+f.target);
  if(!(f.public?publicRoots:privateRoots).some(p=>f.target.startsWith(p))||/\.(php[0-9]?|phtml|phar|cgi|pl|sh)$/i.test(f.target))fail('Unexpected target: '+f.target);
  if(!isSha(f.source_sha256)||!isSha(f.sha256)||!Number.isSafeInteger(f.source_size)||f.source_size<0||!Number.isSafeInteger(f.size)||f.size<0)fail('Invalid asset hash or size: '+f.target);
  if(f.public&&/\.html?$/i.test(f.target)&&!f.html_allowed)fail('Public HTML lacks explicit allowance: '+f.target);
  const old=targets.get(f.target);if(old&&(old.sha256!==f.sha256||old.size!==f.size||old.public!==f.public||old.html_allowed!==f.html_allowed))fail('Conflicting target: '+f.target);
  const source=sources.get(f.source);if(source&&(source.source_sha256!==f.source_sha256||source.source_size!==f.source_size))fail('Conflicting source: '+f.source);
  targets.set(f.target,f);sources.set(f.source,f);
 }
 return {data,bytes,targets};
}
function checkTarget(output,target,record){
 const file=noLinks(path.join(output,relative(target)));
 let current=path.dirname(file);
 while(current!==output&&current!==path.dirname(current)){
  if(fs.existsSync(current)&&!fs.statSync(current).isDirectory())fail('Output parent is not a directory: '+current);
  current=path.dirname(current);
 }
 if(fs.existsSync(file)&&!expectedFile(file,record))fail('Existing target differs; refusing overwrite: '+target);
 return file;
}
function compiler(){
 const modules=process.env.CMS_ASSET_NODE_MODULES;
 if(!modules||!path.isAbsolute(modules)||!fs.statSync(modules).isDirectory())fail('CMS_ASSET_NODE_MODULES must point to the locked external node_modules');
 const require=createRequire(path.join(modules,'../package.json'));
 for(const [name,version]of [['terser','5.44.1'],['clean-css','5.3.3']]){
  const resolved=fs.realpathSync(require.resolve(name+'/package.json'));
  if(!resolved.startsWith(fs.realpathSync(modules)+path.sep)||require(name+'/package.json').version!==version)fail('Unexpected compiler source or version: '+name);
 }
 return {minify:require('terser').minify,CleanCSS:require('clean-css')};
}
function unpackInput(archive,input,manifestPath,expected){
 noLinks(archive);if(!isSha(expected)||sha(fs.readFileSync(archive))!==expected)fail('Build source archive SHA mismatch');
 execFileSync(process.env.CMS_ASSET_PYTHON_BINARY||'python3',['-c',String.raw`
import tarfile,pathlib,sys,json,hashlib
archive,root,manifest=sys.argv[1:]; root=pathlib.Path(root)
expected={f['source']:f for f in json.load(open(manifest))['files']}
with tarfile.open(archive,'r:xz') as tar:
 members=tar.getmembers(); names=[m.name for m in members]
 if len(names)!=len(set(names)): raise ValueError('Duplicate source archive members')
 for m in members:
  p=pathlib.PurePosixPath(m.name)
  if not m.isfile() or p.is_absolute() or any(not x or x.startswith('.') for x in m.name.split('/')) or '\\' in m.name or m.name not in expected: raise ValueError('Unsafe source archive member')
  f=expected[m.name]; data=tar.extractfile(m).read()
  if len(data)!=f['source_size'] or hashlib.sha256(data).hexdigest()!=f['source_sha256']: raise ValueError('Source archive member SHA mismatch')
 for m in members:
  target=root/m.name; target.parent.mkdir(parents=True,exist_ok=True)
  with target.open('xb') as output: output.write(tar.extractfile(m).read())
`,archive,input,manifestPath],{stdio:['ignore','pipe','pipe']});
}
async function main(){
 const opt=options();const manifest=loadManifest();
 if(!opt.apply){
  if(opt.input||opt.output)fail('--input/--output require --apply');
  const failures=[];
  for(const [target,record]of manifest.targets)if(!expectedFile(path.join(root,target),record))failures.push(target);
  console.log(JSON.stringify({mode:'verify',records:manifest.data.files.length,unique_targets:manifest.targets.size,failures}));return failures.length?1:0;
 }
 const output=opt.output||root;noLinks(output);
 if(fs.existsSync(output)&&!fs.statSync(output).isDirectory())fail('Output is not a directory');
 const manifestRecord={size:manifest.bytes.length,sha256:sha(manifest.bytes)};
 for(const[target,record]of manifest.targets)checkTarget(output,target,record);
 checkTarget(output,manifestRelative,manifestRecord);
 const {minify,CleanCSS}=compiler();
 const temp=fs.realpathSync(fs.mkdtempSync(path.join(os.tmpdir(),'cms-asset-build-')));
 const fallbacks=[];const staged=new Map();const created=[];
 try{
  let input=opt.input;
  if(input){if(!fs.statSync(input).isDirectory())fail('Input must be an existing directory');}
  else{input=path.join(temp,'input');fs.mkdirSync(input);unpackInput(path.join(root,'app/cms/resources/asset-build-inputs.tar.xz'),input,path.join(root,manifestRelative),manifest.data.build_sources_sha256);}
  for(const f of manifest.data.files){
   const source=noLinks(path.join(input,f.source));const current=noLinks(path.join(root,f.target));
   let origin=source;
   if(!regular(source)){
    if(f.minify)fail('Missing original compiler input: '+f.source);
    origin=current;
   }
   if(!regular(origin))fail('Missing original asset: '+f.source);
   const original=fs.readFileSync(origin);
   if(original.length!==f.source_size||sha(original)!==f.source_sha256)fail('Source SHA mismatch: '+f.source);
   let bytes=original;
   if(f.minify){
    try{
     if(f.source.endsWith('.js')){
      const result=await minify(original.toString(),{compress:false,mangle:false,format:{comments:/^!|@preserve|@license|copyright|license/i,ascii_only:false},sourceMap:false});bytes=Buffer.from(result.code+'\n');
     }else if(f.source.endsWith('.css')){
      const result=new CleanCSS({level:0,rebase:false,inline:['none'],format:{breaks:{afterComment:true}},specialComments:'all'}).minify(original.toString());
      if(result.errors.length)throw Error(result.errors.join(';'));bytes=Buffer.from(result.styles+'\n');
     }else fail('Unsupported compiler source: '+f.source);
     if(bytes.length>=original.length)bytes=original;
    }catch(error){
     if(!f.build_warning)throw error;
     fallbacks.push({file:f.source,error:error.message});bytes=original;
    }
   }
   if(bytes.length!==f.size||sha(bytes)!==f.sha256)fail('Rebuilt asset differs from locked manifest: '+f.source);
   if(staged.has(f.target))continue;
   const target=path.join(temp,'stage',f.target);fs.mkdirSync(path.dirname(target),{recursive:true});fs.writeFileSync(target,bytes,{flag:'wx'});staged.set(f.target,{file:target,record:f});
  }
  const manifestStage=path.join(temp,'manifest.json');fs.writeFileSync(manifestStage,manifest.bytes,{flag:'wx'});staged.set(manifestRelative,{file:manifestStage,record:manifestRecord});
  // All compiler results and output conflicts pass before any final asset is created.
  for(const[target,item]of staged)checkTarget(output,target,item.record);
  for(const[target,item]of staged){
   const final=checkTarget(output,target,item.record);if(fs.existsSync(final))continue;
   fs.mkdirSync(path.dirname(final),{recursive:true});checkTarget(output,target,item.record);
   const temporary=path.join(path.dirname(final),'.asset-'+crypto.randomBytes(12).toString('hex'));
   try{
    fs.copyFileSync(item.file,temporary,fs.constants.COPYFILE_EXCL);fs.chmodSync(temporary,0o644);
    checkTarget(output,target,item.record);fs.linkSync(temporary,final);const stat=fs.statSync(final);created.push({file:final,hash:item.record.sha256,dev:stat.dev,ino:stat.ino});
   }finally{if(fs.existsSync(temporary))fs.unlinkSync(temporary);}
  }
  console.log(JSON.stringify({mode:'built',records:manifest.data.files.length,unique_targets:manifest.targets.size,created_files:created.length,manifest_unchanged:true,unchanged_fallbacks:fallbacks}));return 0;
 }catch(error){
  for(const entry of created.reverse()){
   if(regular(entry.file)){const stat=fs.statSync(entry.file);if(stat.dev===entry.dev&&stat.ino===entry.ino&&sha(fs.readFileSync(entry.file))===entry.hash)fs.unlinkSync(entry.file);}
  }
  throw error;
 }finally{fs.rmSync(temp,{recursive:true,force:true});}
}
try{process.exitCode=await main();}catch(error){console.error(JSON.stringify({ok:false,error:error.message}));process.exitCode=1;}
