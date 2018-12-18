<?php 
 if(!defined('LMXCMS')){exit();} 
 //本文件为缓存文件 无需手动更改
 ?><tr>
<td align='right' width='12%'>产品图片：</td>
<td width='88%'><p id='pic-p'><{if $update}><img width='220' src='<{$pic}>'/><{/if}></p><input type='text' id='pic' name='pic' class='inputText inputWidth' value='<{if $update}><{$pic}><{else}><{/if}>' /> <input type='button' value='上传' class='inputSub1' onclick="selectUpload(1,'file/d/<{$classData.classpath}>','pic',0)" /></td>
</tr>
<tr>
<td align='right' width='12%'>产品图片集：</td>
<td width='88%'><div class='morefile'><h3>图片列表</h3><ul id='duotp'><{if $update && $duotp}><{assign var='duotp' value='#####'|explode:$duotp}><{foreach from=$duotp item=v}><li><input type='text' name='duotp[]' class='inputText inputWidth' value='<{$v}>'> <span class='red'>移除</span><img style='max-width:120px; max-height:120px;' src='<{$v}>'/></li><{/foreach}><{/if}></ul><input type='button' value='上传图片' class='inputSub1' onclick="selectUpload(1,'file/d/<{$classData.classpath}>','duotp',1)" />&nbsp;<input type='button' class='inputSub1 addfile' value='远程地址' /></div></td>
</tr>
<tr>
<td align='right' width='12%'>视频地址：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='video' id='video' value='<{if $update}><{$video}><{else}><{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>排序：</td>
<td width='88%'><input type='text' class='inputText inputWidth' name='sort' id='sort' value='<{if $update}><{$sort}><{else}>0<{/if}>' /></td>
</tr>
<tr>
<td align='right' width='12%'>正文：</td>
<td width='88%'><textarea id='content' name='content'  style='width:100%;height:300px;'><{if $update}><{$content}><{else}><{/if}></textarea><script type='text/javascript'>UE.getEditor('content',{toolbars:[['fullscreen','source','|','undo','redo','|','bold','italic','underline','fontborder','strikethrough','superscript','subscript','removeformat','formatmatch','autotypeset','blockquote','pasteplain','|','forecolor','backcolor','insertorderedlist','insertunorderedlist','selectall','cleardoc','|','rowspacingtop','rowspacingbottom','lineheight','|','customstyle','paragraph','fontfamily','fontsize','|','directionalityltr','directionalityrtl','indent','|','justifyleft','justifycenter','justifyright','justifyjustify','|','touppercase','tolowercase','|','link','unlink','anchor','|','imagenone','imageleft','imageright','imagecenter','|','insertimage','emotion','music','attachment','map','gmap','insertframe','insertcode','webapp','pagebreak','template','background','|','horizontal','date','time','spechars','|','inserttable','deletetable','insertparagraphbeforetable','insertrow','deleterow','insertcol','deletecol','mergecells','mergeright','mergedown','splittocells','splittorows','splittocols','charts','|','print','preview','searchreplace','drafts']],imagePath:'/file/d/<{$classData.classpath}>/',imageUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/',filePath:'/file/d/<{$classData.classpath}>/',fileUrl:'/admin.php?m=Edit&a=editUpload&path=/file/d/<{$classData.classpath}>/'})</script></td>
</tr>
