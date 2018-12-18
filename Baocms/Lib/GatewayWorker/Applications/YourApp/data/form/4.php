<?php 
 if(!defined('LMXCMS')){exit();} 
 //本文件为缓存文件 无需手动更改
 ?><tr>
<td align='right' width='12%'>封面图：</td>
<td width='88%'><p id='pic-p'><{if $update}><img width='220' src='<{$pic}>'/><{/if}></p><input type='text' id='pic' name='pic' class='inputText inputWidth' value='<{if $update}><{$pic}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(1,'file/d/<{$classData.classpath}>','pic',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>审核状态：</td>
<td width='88%'><select name='state' id='state'><option value='0'<{if $update && $state eq '0'}> selected<{/if}>>审核中</option><option value='1'<{if $update && $state eq '1'}> selected<{/if}>>审核通过</option><option value='2'<{if $update && $state eq '2'}> selected<{/if}>>成功项目</option><option value='-1'<{if $update && $state eq '-1'}> selected<{/if}>>未通过</option></select></td>
</tr>
<tr>
<td align='right' width='12%'>中标人：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='bider' id='bider' value='<{if $update}><{$bider}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>目标金额：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='garget' id='garget' value='<{if $update}><{$garget}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>文件上传：</td>
<td width='88%'><input type='text' id='file' name='file' class='inputText inputWidth' value='<{if $update}><{$file}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(2,'file/d/<{$classData.classpath}>','file',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>发布人：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='publisher' id='publisher' value='<{if $update}><{$publisher}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>排序：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='sort' id='sort' value='<{if $update}><{$sort}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>截止日期：</td>
<td width='88%'><input type='text' name='overtime' id='overtime' value='<{if $update}><{$overtime|date_format:'%Y-%m-%d %H:%M:%S'}><{else}><{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}><{/if}>'><input type='button' class='inputSub1' value='获取当前时间' onclick="document.getElementById('overtime').value='<{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}>'"></td>
</tr>
<tr>
<td align='right' width='12%'>施工地址：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='address' id='address' value='<{if $update}><{$address}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>项目名称：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='project' id='project' value='<{if $update}><{$project}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>正文：</td>
<td width='88%'><textarea id='content' name='content'  style='width:100%;height:300px;'><{if $update}><{$content}><{else}><{/if}></textarea><script type='text/javascript'>UE.getEditor('content',{toolbars:[['fullscreen','source','|','undo','redo','|','bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat','formatmatch','autotypeset','blockquote','pasteplain','|','forecolor','backcolor','insertorderedlist','insertunorderedlist','selectall','cleardoc','|','rowspacingtop','rowspacingbottom','lineheight','|','customstyle','paragraph','fontfamily','fontsize','|','directionalityltr','directionalityrtl','indent','|','justifyleft','justifycenter','justifyright','justifyjustify','|','touppercase','tolowercase','|','link','unlink','anchor','|','imagenone','imageleft','imageright','imagecenter','|','insertimage','emotion','music','attachment','map','gmap','insertframe','insertcode','webapp','pagebreak','template','background','|','horizontal','date','time','spechars','|','inserttable','deletetable','insertparagraphbeforetable','insertrow','deleterow','insertcol','deletecol','mergecells','mergeright','mergedown','splittocells','splittorows','splittocols','charts','|','print','preview','searchreplace','drafts']],imagePath:'/file/d/<{$classData.classpath}>/',imageUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/',filePath:'/file/d/<{$classData.classpath}>/',fileUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/'})</script></td>
</tr>
