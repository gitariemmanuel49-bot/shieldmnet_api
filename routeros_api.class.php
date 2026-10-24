<?php
class RouterosAPI {
 public $debug=false;
 private $socket;
 function connect($ip,$login,$password){
  $this->socket=fsockopen($ip,8728,$e,$s,3);
  if(!$this->socket) return false;
  $this->write('/login');
  $r=$this->read();
  $chal=$r[0]['!done']['ret'] ?? '';
  if(!$chal){ $chal=$r[0]['!trap'][0]['ret'] ?? ''; }
  $this->write('/login',false);
  $this->write('=name='.$login,false);
  $this->write('=response=00'.md5(chr(0).$password.pack('H*',$chal)));
  $r=$this->read();
  return isset($r[0]['!done']);
 }
 function comm($com,$attr=[]){
  $this->write($com,!$attr);
  $i=0; foreach($attr as $k=>$v){ $i++; $this->write('='.$k.'='.$v,$i==count($attr)); }
  return $this->read();
 }
 function disconnect(){ if($this->socket) fclose($this->socket); }
 private function write($str,$end=true){
  $len=strlen($str);
  if($len<128) fwrite($this->socket,chr($len).$str);
  else if($len<16384){ $len|=0x8000; fwrite($this->socket,chr(($len>>8)&255).chr($len&255).$str); }
  else { $len|=0xC00000; fwrite($this->socket,chr(($len>>16)&255).chr(($len>>8)&255).chr($len&255).$str); }
  if($end) fwrite($this->socket,chr(0));
 }
 private function read(){
  $ret=[]; $c=-1;
  while(true){
   $b=ord(fread($this->socket,1)); if($b==0) break;
   if($b<128) $l=$b;
   else if(($b&192)==128){ $l=(($b&63)<<8)+ord(fread($this->socket,1)); }
   else { $l=(($b&31)<<16)+(ord(fread($this->socket,1))<<8)+ord(fread($this->socket,1)); }
   $s=''; while(strlen($s)<$l) $s.=fread($this->socket,$l-strlen($s));
   if($s=='!re'||$s=='!done'||$s=='!trap'){ $ret[++$c]=[$s=>[]]; }
   else { $kv=explode('=',$s,3); if(count($kv)>=3) $ret[$c][$kv[0]][$kv[1]]=$kv[2]; }
  } return $ret;
 }
}
?>
