<?php 
 if(!defined('LMXCMS')){exit();} 
 //本文件为缓存文件 无需手动更改
 ?><tr>
<td align='right' width='12%'>职位：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='position' id='position' value='<{if $update}><{$position}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>招聘人数：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='nums' id='nums' value='<{if $update}><{$nums}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>工资待遇：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='wage' id='wage' value='<{if $update}><{$wage}><{else}>面议<{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>招聘部门：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='department' id='department' value='<{if $update}><{$department}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>截止日期：</td>
<td width='88%'><input type='text' name='overtime' id='overtime' value='<{if $update}><{$overtime|date_format:'%Y-%m-%d %H:%M:%S'}><{else}><{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}><{/if}>'><input type='button' class='inputSub1' value='获取当前时间' onclick="document.getElementById('overtime').value='<{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>'"></td>
</tr>
<tr>
<td align='right' width='12%'>图片：</td>
<td width='88%'><input type='text' id='pic' name='pic' class='inputText inputWidth' value='<{if $update}><{$pic}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(1,'file/d/<{$classData.classpath}>','pic',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>正文：</td>
<td width='88%'><textarea id='content' name='content'  style='width:100%;height:300px;'><{if $update}><{$content}><{else}><{/if}></textarea><script type='text/javascript'>UE.getEditor('content',{toolbars:[['fullscreen','source','|','undo','redo','|','bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat','formatmatch','autotypeset','blockquote','pasteplain','|','forecolor','backcolor','insertorderedlist','insertunorderedlist','selectall','cleardoc','|','rowspacingtop','rowspacingbottom','lineheight','|','customstyle','paragraph','fontfamily','fontsize','|','directionalityltr','directionalityrtl','indent','|','justifyleft','justifycenter','justifyright','justifyjustify','|','touppercase','tolowercase','|','link','unlink','anchor','|','imagenone','imageleft','imageright','imagecenter','|','insertimage','emotion','music','attachment','map','gmap','insertframe','insertcode','webapp','pagebreak','template','background','|','horizontal','date','time','spechars','|','inserttable','deletetable','insertparagraphbeforetable','insertrow','deleterow','insertcol','deletecol','mergecells','mergeright','mergedown','splittocells','splittorows','splittocols','charts','|','print','preview','searchreplace','drafts']],imagePath:'/file/d/<{$classData.classpath}>/',imageUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/',filePath:'/file/d/<{$classData.classpath}>/',fileUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/'})</script></td>
</tr>
<tr>
<td align='right' width='12%'>排序：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='sort' id='sort' value='<{if $update}><{$sort}><{else}>0<{/if}>' /></td>
</tr>
