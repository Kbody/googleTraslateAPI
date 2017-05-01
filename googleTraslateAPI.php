<?php
/**
 * By 1229598328@qq.com
 */
header('Content-Type: text/html; charset=utf-8');
class googleTraslateAPI {
    function __construct() {
    }
    public function googleTraslate($text = '', $sl = '', $tl = '') {
        $tk_arr = $this->googleTraslate_getTK();
        if (!$tk_arr || $text == '') {
            return false;
        }
        $tk = $tk_arr['tkk'];
        $url = $tk_arr['url'];
        $return = '';
        $text=urlencode($text);
        $tk = $this->TL($text, $tk);
        $sl = $sl == '' ? "zh-CN" : $sl;
        $tl = $tl == '' ? "th" : $tl;
        $parameters = "client=t&" . 
        "sl=" . $sl . "&" . // 本地语言
        "tl=" . $tl . "&" . // 目标语言
        "ie=UTF-8&" . // 输入编码
        "oe=UTF-8&" . // 输出编码
        "hl=zh-CN&" .
        "dt=at&" .
        "dt=bd&" . 
        "dt=ex&" . 
        "dt=ld&" . 
        "dt=md&" . 
        "dt=qca&" . 
        "dt=rw&" . 
        "dt=rm&" . 
        "dt=ss&" . 
        "dt=t&" . 
        "source=btn&" . 
        "ssel=0&" . 
        "tsel=0&" . 
        "kc=0&" . 
        "tk=" . $tk .  //这个值很重要，有客户端提供
        "&q="; // 需要翻译的文字
        $url.= 'translate_a/single?' . $parameters . $text;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $return = curl_exec($ch);
        curl_close($ch);
        if ($return) {
        	$return=explode(',',$return);
        	if (isset($return[0])) {
        		return trim($return[0],'\[\]\"\'');
        	}
        }
        return false;
    }

    private function googleTraslate_getTK() {
        $url_cn = "https://translate.google.cn/";
        $url_sg = "https://translate.google.com.sg/";
        $url_th = "https://translate.google.co.th/";
        $url_co = "https://translate.google.com/";
        $urls = array(
            $url_sg,
            $url_th,
            $url_cn,
            $url_co
        );
        foreach ($urls as $key => $value) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $value);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $conts = curl_exec($ch);
            curl_close($ch);

            if (preg_match('/TKK=eval\(\'\(\(function\(\)\{var\s+a\\\x3d(-?\d+);var\s+b\\\\x3d(-?\d+);return\s+(\d+)\+/i', $conts, $matches)) {
                $tkk = $matches[3] . '.' . ($matches[1] + $matches[2]);
                return array(
                    'tkk' => $tkk,
                    'url' => $value
                );
            }
            break;
        }
        return false;
    }
    //这个函数是无符号右移
    private function shr32($x, $bits) {
        if ($bits <= 0) {
            return $x;
        }
        if ($bits >= 32) {
            return 0;
        }
        $bin = decbin($x);
        $l = strlen($bin);
        if ($l > 32) {
            $bin = substr($bin, $l - 32, 32);
        } elseif ($l < 32) {
            $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
        }
        return bindec(str_pad(substr($bin, 0, 32 - $bits) , 32, '0', STR_PAD_LEFT));
    }

    private function charCodeAt($str, $index) {
        $char = mb_substr($str, $index, 1, 'UTF-8');
        if (mb_check_encoding($char, 'UTF-8')) {
            $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
            return hexdec(bin2hex($ret));
        } else {
            return null;
        }
    }

    private function RL($a, $b) {
        for ($c = 0; $c < strlen($b) - 2; $c+= 3) {
            $d = $b{$c + 2};
            $d = $d >= 'a' ? $this->charCodeAt($d, 0) - 87 : intval($d);
            $d = $b{$c + 1} == '+' ? $this->shr32($a, $d) : $a << $d;
            $a = $b{$c} == '+' ? ($a + $d & 4294967295) : $a ^ $d;
        }
        return $a;
    }

    private function TL($a, $TKK) {
        $tkk = explode('.', $TKK);
        $b = $tkk[0];
        for ($d = array() , $e = 0, $f = 0; $f < mb_strlen($a, 'UTF-8'); $f++) {
            $g = $this->charCodeAt($a, $f);
            if (128 > $g) {
                $d[$e++] = $g;
            } else {
                if (2048 > $g) {
                    $d[$e++] = $g >> 6 | 192;
                } else {
                    if (55296 == ($g & 64512) && $f + 1 < mb_strlen($a, 'UTF-8') && 56320 == ($this->charCodeAt($a, $f + 1) & 64512)) {
                        $g = 65536 + (($g & 1023) << 10) + ($this->charCodeAt($a, ++$f) & 1023);
                        $d[$e++] = $g >> 18 | 240;
                        $d[$e++] = $g >> 12 & 63 | 128;
                    } else {
                        $d[$e++] = $g >> 12 | 224;
                        $d[$e++] = $g >> 6 & 63 | 128;
                    }
                }
                $d[$e++] = $g & 63 | 128;
            }
        }
        $a = $b;
        for ($e = 0; $e < count($d); $e++) {
            $a+= $d[$e];
            $a = $this->RL($a, '+-a^+6');
        }
        $a = $this->RL($a, "+-3^+b+-f");
        $a^= $tkk[1];
        if (0 > $a) {
            $a = ($a & 2147483647) + 2147483648;
        }
        $a = fmod($a, pow(10, 6));
        return $a . "." . ($a ^ $b);
    }
}
$GTAPI = new googleTraslateAPI();
echo $GTAPI->googleTraslate("需要翻译的字符");
?>
