/*! CMS template source editor. Local CodeMirror 5 integration; project maintained. */
(function(window, document) {
'use strict';
const CM=window.CodeMirror;
const modes={html:'htmlmixed',javascript:'javascript',css:'css',json:{name:'javascript',json:true},xml:'application/xml',text:'text/plain'};
const labels={html:'HTML / 模板',javascript:'JavaScript',css:'CSS',json:'JSON',xml:'XML',text:'纯文本'};
const dialect=document.getElementById('content')?.dataset.templateDialect==='webman'?'webman':'eyou';
const snippets=['{eyou:global name="web_name" escape="html" /}','{eyou:global name="web_description" escape="html" /}','{eyou:include file="header.htm" /}','{eyou:include file="footer.htm" /}','{eyou:channel type="top" row="10" id="field"}','{/eyou:channel}','{eyou:arclist row="6" titlelen="40" id="field"}','{/eyou:arclist}','{eyou:list pagesize="12" id="field"}','{/eyou:list}','{eyou:if condition="$field.litpic != \'\'"}','{eyou:else /}','{/eyou:if}','{eyou:position /}','{eyou:pagelist /}','{$field.title}','{$field.arcurl}','{$field.litpic}','{$eyou.field.seo_title}','{$eyou.field.content}'].map(s=>dialect==='webman'?s.replaceAll('eyou:','webman:').replaceAll('$eyou.','$webman.'):s);
function language(path,content){const ext=path.split('.').pop().toLowerCase();if(['htm','html'].includes(ext))return 'html';if(ext==='js')return 'javascript';if(ext==='css')return 'css';const value=content.trim();if(/^[\[{]/.test(value)){try{JSON.parse(value);return 'json';}catch(_){}}if(/^<\?xml\b/i.test(value))return 'xml';if(/^(?:<!doctype\s+html|<(?:html|div|main|section|body|p|a|style|script)\b|\{eyou:)/i.test(value))return 'html';if(/^(?:const|let|var|function|import|export|\/\*[\s\S]*?\*\/\s*(?:const|let|var))\b/.test(value))return 'javascript';if(/^[.#@][\w\s.#,:>+\-()\[\]="']+\{/.test(value))return 'css';return 'text';}
function overlay(){return {token(stream){if(stream.match(/\{(?:\/?(?:eyou|webman):|\$)(?:[^}"']|"[^"]*"|'[^']*')*\}/))return 'variable-2';if(stream.peek()==='{')stream.next();while(!stream.eol()&&stream.peek()!=='{')stream.next();return null;}};}
if(CM)CM.defineMode('cms-html-template',config=>CM.overlayMode(CM.getMode(config,'htmlmixed'),overlay()));
function create(textarea,options){
 if(!CM)throw Error('代码编辑器加载失败，请刷新页面重试');
 let current='text',automatic=true,loading=false;
 const select=document.getElementById('code-language'),status=document.getElementById('code-position');
 const editor=CM.fromTextArea(textarea,{lineNumbers:true,lineWrapping:false,theme:'cms-dark',indentUnit:2,tabSize:2,indentWithTabs:false,matchBrackets:true,autoCloseBrackets:true,autoCloseTags:true,readOnly:false,viewportMargin:12,extraKeys:{
  'Ctrl-S':()=>options.save(),'Cmd-S':()=>options.save(),
  'Ctrl-Space':complete,'Cmd-Space':complete,'Alt-/':complete,
  'Shift-Alt-F':format,'Shift-Ctrl-F':format,'Shift-Cmd-F':format,
  'Tab':cm=>cm.somethingSelected()?cm.indentSelection('add'):cm.replaceSelection(' '.repeat(cm.getOption('indentUnit')),'end','+input'),
  'Shift-Tab':cm=>cm.indentSelection('subtract'),
  'Ctrl-F':cm=>cm.execCommand('find'),'Cmd-F':cm=>cm.execCommand('find'),
  'Ctrl-H':cm=>cm.execCommand('replace'),'Cmd-Alt-F':cm=>cm.execCommand('replace')
 }});
 editor.getInputField().setAttribute('aria-label','模板源代码');
 editor.getInputField().setAttribute('data-cms-filter',''); // Source textarea alone owns the dirty baseline.
 editor.getWrapperElement().setAttribute('data-cms-filter',''); // Find/replace inputs are navigation, not source changes.
 function position(){const p=editor.getCursor();status.textContent=`${labels[current]} · 第 ${p.line+1} 行，第 ${p.ch+1} 列 · ${editor.lineCount()} 行`;}
 function setMode(value){current=value;editor.setOption('mode',value==='html'?'cms-html-template':modes[value]);position();}
 function sync(){editor.save();textarea.dispatchEvent(new Event('input',{bubbles:true}));position();}
 function complete(cm=editor){if(cm.getOption('readOnly'))return;cm.showHint({container:document.getElementById('editor'),completeSingle:false,hint(instance){const cursor=instance.getCursor(),line=instance.getLine(cursor.line),before=line.slice(0,cursor.ch),fragment=before.match(/\{(?:\/?(?:eyou|webman):|\$)[^{}]*$|\{$/);if(fragment){const start=cursor.ch-fragment[0].length;return {list:snippets.filter(s=>s.startsWith(fragment[0])),from:CM.Pos(cursor.line,start),to:CM.Pos(cursor.line,cursor.ch+(line.charAt(cursor.ch)==='}'?1:0))};}const helper=current==='html'?CM.hint.html:current==='css'?CM.hint.css:current==='javascript'?CM.hint.javascript:null;const result=helper?.(instance,{})||CM.hint.anyword(instance,{});return result;}});}
 function format(){if(editor.getOption('readOnly'))return;const original=editor.getValue();try{
  // Protect template expressions (including quoted attributes) from generic formatters.
  const tokens=[],prefix='CMS_TEMPLATE_'+Math.random().toString(36).slice(2)+'_';
  const source=original.replace(/\{(?:\/?(?:eyou|webman):|\$)(?:[^}"']|"[^"]*"|'[^']*')*\}/g,token=>{tokens.push(token);return prefix+(tokens.length-1)+'_END';});
  const settings={indent_size:2,preserve_newlines:true,max_preserve_newlines:2,end_with_newline:original.endsWith('\n'),wrap_line_length:0,extra_liners:[],unformatted:['pre','textarea'],content_unformatted:['pre','textarea']};
  const formatter=current==='html'||current==='xml'?window.html_beautify:current==='css'?window.css_beautify:current==='javascript'||current==='json'?window.js_beautify:null;
  if(!formatter){options.message('纯文本不进行自动格式化，请先选择代码语言');return;}
  let output=formatter(source,settings);
  for(let i=0;i<tokens.length;i++){const key=prefix+i+'_END';if(output.split(key).length!==2)throw Error('格式化未完成：模板标签无法安全保留');output=output.replace(key,tokens[i]);}
  if(current==='json')JSON.parse(output);
  if(output!==original){const cursor=editor.getCursor(),scroll=editor.getScrollInfo();editor.operation(()=>{editor.replaceRange(output,CM.Pos(0,0),CM.Pos(editor.lastLine(),editor.getLine(editor.lastLine()).length),'+format');editor.setCursor(cursor);editor.scrollTo(scroll.left,scroll.top);});}
  options.message('已格式化，可用 Ctrl / ⌘ + Z 撤销');
 }catch(error){options.message(error.message||'格式化失败，内容已保留');}editor.focus();}
 select.onchange=()=>{automatic=select.value==='auto';setMode(automatic?language(options.path(),editor.getValue()):select.value);editor.focus();};
 editor.on('change',()=>{if(!loading){sync();options.change?.();if(automatic&&/\.txt$/i.test(options.path())){const next=language(options.path(),editor.getValue());if(next!==current)setMode(next);}}});
 editor.on('cursorActivity',position);
 editor.on('inputRead',(cm,change)=>{if(change.origin!=='+input'||cm.state.completionActive||cm.getOption('readOnly'))return;const typed=change.text.join('');if(typed.length===1&&/[\w<{:$-]/.test(typed))complete(cm);});
 // Capture browser save shortcuts only within the editor, including a completion popup.
 editor.getWrapperElement().addEventListener('keydown',event=>{if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='s'){event.preventDefault();event.stopPropagation();options.save();}},true);
 document.getElementById('code-format').onclick=format;
 document.getElementById('code-complete').onclick=()=>complete();
 document.getElementById('code-search').onclick=()=>editor.execCommand('find');
 return {load(value,path,readonly){loading=true;editor.setValue(value);editor.clearHistory();editor.setOption('readOnly',readonly);select.value='auto';automatic=true;setMode(language(path,value));textarea.value=value;loading=false;for(const id of ['code-format','code-complete'])document.getElementById(id).disabled=readonly;requestAnimationFrame(()=>{editor.refresh();editor.focus();});},accept(value,path,readonly){if(value!==editor.getValue()){const cursor=editor.getCursor(),scroll=editor.getScrollInfo();this.load(value,path,readonly);editor.setCursor(cursor);editor.scrollTo(scroll.left,scroll.top);}else{editor.save();editor.setOption('readOnly',readonly);position();}},value:()=>editor.getValue(),readOnly(value){editor.setOption('readOnly',value);for(const id of ['code-format','code-complete'])document.getElementById(id).disabled=value;},refresh:()=>editor.refresh(),detect(){if(automatic)setMode(language(options.path(),editor.getValue()));}};
}
window.CmsTemplateEditor={create};
})(window,document);
