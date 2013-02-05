<?php
class FDFS{
	public $tracker=null;
	public $storage=null;
	public $group_name=null;
	public function __construct($group_name=null){
		if($group_name){
			$this->group_name = $group_name;
		}else{
			$this->tracker = fastdfs_tracker_get_connection();
			$this->storage = fastdfs_tracker_query_storage_store();
			if(!$this->tracker||!$this->storage){
				error_log("errno1: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
	            exit(1);
			}
		}
	}
	/**
	 * 设置组名
	 * @param $group_name 组名
	 */
	public function setGroupName($group_name){
		$this->group_name = $group_name;
	}
	/**
	文件上传
	$file 要上传的文件的绝对路径
	$meta_list 数组，文件附带的元元素，比如 array('width'=>1024, 'height'=>768)

	@return 成功返回数组 array('group_name'=>'xx','filename'=>'yy');
			失败返回false
	*/
	public function upload($file,$group_name=null,$meta=array()){
		if($group_name){
			$this->group_name = $group_name;
		}
		if($this->group_name){
			$file_info = fastdfs_storage_upload_by_filename($file, null, $meta, $this->group_name);	
		}else{
			$file_info = fastdfs_storage_upload_by_filename($file, null, $meta, null, $this->tracker, $this->storage);
		}
		if($file_info['group_name']){
			$this->group_name = $file_info['group_name'];
		}
		return $file_info;
	}
	/**
	文件读取
	$group_name 组名
	$file_id 文件名
	$file_offset 读取的开始位置
	$download_bytes	读取的大小

	@return 成功返回文件内容;
			失败返回false
	*/
	public function download($group_name, $file_id, $file_offset=0,$download_bytes=0){
		$file_content = fastdfs_storage_download_file_to_buff($group_name, $file_id, $file_offset, $download_bytes);
		return $file_content;

	}
	/**
	文件删除
	$group_name 组名
	$file_id 文件名

	@return 成功返回true
			失败返回false
	*/
	public function delete($group_name, $file_id){
		return fastdfs_storage_delete_file($group_name, $file_id);
	}
	/**
	返回错误信息
	*/
	public function error(){
		return fastdfs_get_last_error_info();
	}
}