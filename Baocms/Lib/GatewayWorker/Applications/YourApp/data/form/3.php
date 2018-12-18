<?php 
 if(!defined('LMXCMS')){exit();} 
 //本文件为缓存文件 无需手动更改
 ?><tr>
<td align='right' width='12%'>缩略图：</td>
<td width='88%'><p id='pic-p'><{if $update}><img width='220' src='<{$pic}>'/><{/if}></p><input type='text' id='pic' name='pic' class='inputText inputWidth' value='<{if $update}><{$pic}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(1,'file/d/<{$classData.classpath}>','pic',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>效果图：</td>
<td width='88%'><p id='xpic-p'><{if $update}><img width='220' src='<{$xpic}>'/><{/if}></p><input type='text' id='xpic' name='xpic' class='inputText inputWidth' value='<{if $update}><{$xpic}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(1,'file/d/<{$classData.classpath}>','xpic',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>上传文件：</td>
<td width='88%'><input type='text' id='file' name='file' class='inputText inputWidth' value='<{if $update}><{$file}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(2,'file/d/<{$classData.classpath}>','file',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>发布人：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='puber' id='puber' value='<{if $update}><{$puber}><{else}>0<{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>格式：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='format' id='format' value='<{if $update}><{$format}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>文章状态：</td>
<td width='88%'><select name='state' id='state'><option value='1'<{if $update && $state eq '1'}> selected<{/if}>>已发布</option><option value='0'<{if $update && $state eq '0'}> selected<{/if}>>审核中</option></select></td>
</tr>
<tr>
<td align='right' width='12%'>排序：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='sort' id='sort' value='<{if $update}><{$sort}><{else}>0<{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>正文：</td>
<td width='88%'><textarea id='content' name='content'  style='width:100%;height:300px;'><{if $update}><{$content}><{else}><{/if}></textarea><script type='text/javascript'>UE.getEditor('content',{toolbars:[['fullscreen','source','|','undo','redo','|','bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat','formatmatch','autotypeset','blockquote','pasteplain','|','forecolor','backcolor','insertorderedlist','insertunorderedlist','selectall','cleardoc','|','rowspacingtop','rowspacingbottom','lineheight','|','customstyle','paragraph','fontfamily','fontsize','|','directionalityltr','directionalityrtl','indent','|','justifyleft','justifycenter','justifyright','justifyjustify','|','touppercase','tolowercase','|','link','unlink','anchor','|','imagenone','imageleft','imageright','imagecenter','|','insertimage','emotion','music','attachment','map','gmap','insertframe','insertcode','webapp','pagebreak','template','background','|','horizontal','date','time','spechars','|','inserttable','deletetable','insertparagraphbeforetable','insertrow','deleterow','insertcol','deletecol','mergecells','mergeright','mergedown','splittocells','splittorows','splittocols','charts','|','print','preview','searchreplace','drafts']],imagePath:'/file/d/<{$classData.classpath}>/',imageUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/',filePath:'/file/d/<{$classData.classpath}>/',fileUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/'})</script></td>
</tr>
