"""Historical first import tool, not a regeneration path for maintained adapters."""
from pathlib import Path
import re,json,argparse
parser=argparse.ArgumentParser(description=__doc__)
parser.add_argument('--initial-import',action='store_true',help='Import only into an empty compatibility tree')
args=parser.parse_args()
if not args.initial_import:
 parser.error('Use --initial-import only for an empty tree. Existing adapters contain manual compatibility fixes.')
project=Path(__file__).resolve().parents[3]
source=project.parent/'EyouCMS-V1.7.7-UTF8-SP1'
out=project/'app/cms/compat'
files={source/'core/library/think/Template.php':out/'Template.php',source/'core/library/think/template/TagLib.php':out/'template/TagLib.php',source/'core/library/think/template/taglib/Cx.php':out/'template/taglib/Cx.php',source/'core/library/think/template/taglib/Eyou.php':out/'template/taglib/Eyou.php',source/'core/library/think/template/driver/File.php':out/'template/driver/File.php',source/'core/library/think/Page.php':out/'Page.php'}
for p in (source/'core/library/think/template/taglib/eyou').glob('*.php'): files[p]=out/'template/taglib/eyou'/p.name
files[source/'core/library/think/template/taglib/api/Base.php']=out/'template/taglib/api/Base.php'
for name in ['Arctype','Channeltype','Channelfield','LanguageAttr','Weapp','Citysite','Taglist','Config','ConfigAttribute','Language','LanguageMark','LanguagePack','LanguageAttribute','ForeignPack']:
 files[source/f'application/common/model/{name}.php']=out/f'model/{name}.php'
for name in ['ArctypeLogic','NavigationLogic','CitysiteLogic','BuildhtmlLogic']:
 files[source/f'application/common/logic/{name}.php']=out/f'logic/{name}.php'
files[source/'application/home/logic/FieldLogic.php']=out/'logic/FieldLogic.php'
for p in (source/'application/home/model').glob('*.php'):
 if p.stem != 'Ask': files[p]=out/'model'/p.name
files[source/'application/common/model/ShopGoodsLabel.php']=out/'model/ShopGoodsLabel.php'
files[source/'core/library/think/Paginator.php']=out/'Paginator.php'
for p in (source/'core/library/think/paginator').rglob('*.php'): files[p]=out/'paginator'/p.relative_to(source/'core/library/think/paginator')
replacements=[('think\\template','app\\cms\\compat\\template'),('think\\paginator','app\\cms\\compat\\paginator'),('think\\exception\\TemplateNotFoundException','RuntimeException'),('app\\common\\model','app\\cms\\compat\\model'),('app\\common\\logic','app\\cms\\compat\\logic'),('app\\home\\logic','app\\cms\\compat\\logic'),('app\\home\\model','app\\cms\\compat\\model')]+[(f'think\\{n}',f'app\\cms\\compat\\{n}') for n in ['Db','Request','Config','Cache','Cookie','Session','Lang','Page','Model','Exception','Paginator']]
imports='\n'+''.join('use function app\\cms\\compat\\'+n+';\n' for n in ['config','input','request','cache','cookie','session','model','M','lang','db'])+'use app\\cms\\compat\\Runtime;\n'
if any(dest.exists() for dest in files.values()):
 raise SystemExit('Refusing to overwrite maintained compatibility sources. This tool is only for the initial import.')
manifest=[]
for src,dest in files.items():
 text=src.read_text()
 for old,new in replacements:
  text=text.replace(old.replace('\\','\\\\'),new.replace('\\','\\\\')).replace(old,new)
 text=text.replace('namespace Think;', 'namespace think;')
 text=re.sub(r'namespace think;',r'namespace app\\cms\\compat;',text,flags=re.I)
 text=re.sub(r'(namespace [^;]+;)',lambda m:m[0]+imports,text,count=1)
 # Function-local request/config caches must not survive a worker request.
 text=re.sub(r'\bstatic (\$\w+\s*=)',r'\1',text)
 if 'taglib/eyou' in str(dest) or 'taglib/api' in str(dest):
  text=re.sub(r'\bstatic (\$\w+)',r'public \1',text)
  text=text.replace('self::$','$this->')
 # BuildhtmlLogic needs only its URL/pagination logic; no old controller trait.
 text=text.replace('use \\traits\\controller\\Jump;','use \\app\\cms\\compat\\Jump;')
 dest.parent.mkdir(parents=True,exist_ok=True);dest.write_text(text)
 manifest.append({'source':str(src.relative_to(source)),'target':str(dest.relative_to(project))})
(out/'source-manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n')
print(f'Adapted {len(files)} upstream sources')
