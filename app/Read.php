<?php

namespace DolphinApi;

use Illuminate\Database\Eloquent\Model;

class Read extends Model
{
	protected $fillable = ['pod_id', 'user_id', 'post_id'];
	
	public static function makeAsRead($post, $userId){
		$check = Read::where('pod_id' , $post->pod_id)
		->where('post_id', $post->id)
		->where('user_id', $userId)
		->first();
		if(empty($check)){
			$check = Read::create(array(
				'pod_id' => $post->pod_id,
				'user_id' => $userId,
				'post_id' => $post->id
			));
		}
	}
}

?>