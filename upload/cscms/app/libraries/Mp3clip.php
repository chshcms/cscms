<?php
/**
 * @Cscms open source management system
 * @copyright 2009-2015 chshcms.com. All rights reserved.
 * @Author:Cheng Jie
 * @Dtime:2014-07-26
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MP3截取类，可以截取前或者后的几分钟文件
 */
class Mp3clip {

        var $data_buffer=1048576;
        var $error_message;
        var $old_file;
        var $new_file;
        var $start_time;
        var $file_size;
        var $time_length;
        var $byte_per_second;
        var $input_data;
        var $output_data;
        var $type;

        function mp3_cut($old_file,$time_length,$new_file,$start_time,$type=0){
                $this->old_file=$old_file;
                $this->new_file=$new_file;
                $this->start_time=intval($start_time);
                $this->time_length=intval($time_length);
                $this->type=$type;
                if($this->old_file==$this->new_file){
                     $this->error(L('mp3_01'));
                }
                if($this->start_time==0 || $this->time_length==0){
                     $this->error(L('mp3_02'));
                }
        }

        function cut(){
                $this->set_input_file_size();
                $this->byte_per_second();
                $this->open_input_mp3();
                $this->open_output_mp3();
                $this->make_new_mp3($this->type);
                $this->close_output_mp3();
                $this->close_input_mp3();

        }

        function set_input_file_size(){
                if(!file_exists($this->old_file)){
                     $this->error(L('mp3_03'));
                }else{
                     $this->file_size=@filesize($this->old_file);
                }
        }

        function byte_per_second(){
                $this->byte_per_second=(integer)($this->file_size/$this->time_length);
        }

        function make_new_mp3($type){
                $start_position=$this->start_time*$this->byte_per_second;
                if($type==1){
                        fseek($this->input_data,$start_position);
                        while(!@feof($this->input_data)){
                                @fwrite($this->output_data,@fread($this->input_data,$this->data_buffer));
                        }
                }else{
                        while(@ftell($this->input_data)<=((integer)$start_position)){
                                @fwrite($this->output_data,@fread($this->input_data,($this->byte_per_second/2)));
                        }
                }
        }

        function open_input_mp3(){
                if(file_exists($this->old_file)){
                        $this->input_data=fopen($this->old_file,"r");
                        $result=true;
                }else{
                        $this->error(L('mp3_04'));
                        $result=false;
                }
                return $result;
        }

        function close_input_mp3(){

                if(@fclose($this->input_data)){
                        $result=true;
                }else{
                        $this->error(L('mp3_05'));
                        $result=false;
                }
                return $result;
        }

        function open_output_mp3(){
                $this->output_data=@fopen($this->new_file,"w");
                if($this->output_data){
                        $result=true;
                }else{
                        $this->error(L('mp3_06'));
                        $result=false;
                }
                return $result;
        }

        function close_output_mp3(){
                if(@fclose($this->output_data)){
                        $result=true;
                }else{
                        $this->error(L('mp3_07'));
                        $result=false;
                }
                return $result;
        }

        function error($error_message){
                exit($error_message);
        }
}
