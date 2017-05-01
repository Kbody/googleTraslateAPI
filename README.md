# googleTraslateAPI
谷歌翻译API,google Traslate API
用法
$GTAPI = new googleTraslateAPI();
// $GTAPI->googleTraslate("需要翻译的字符","本地语言","目标语言");
// 本地语言默认为中文,目标语言默认为英文
例1
echo $GTAPI->googleTraslate("需要翻译的字符");
输出：Need to translate the characters
例2
echo $GTAPI->googleTraslate("需要翻译的字符",'zh-CN','th');
输出：ตัวละครตัวนี้ได้รับการแปล
