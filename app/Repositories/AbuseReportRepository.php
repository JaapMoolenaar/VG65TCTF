<?php

namespace DolphinApi\Repositories;

use DB;

use DolphinApi\AbuseReport;

class AbuseReportRepository
{

  static function byPostId( $postId )
  {
    $reports = AbuseReport::where( 'post_id', '=', $postId )->get()->all();

    return self::supercharge( $reports );
  }

  static function byUserId( $userId )
  {
    $reports = AbuseReport::where( 'user_id', '=', $userId )->get()->all();

    return self::supercharge( $reports );
  }

  static function byUserAndPostId( $userId, $postId )
  {
    $reports = AbuseReport::where( 'user_id', $userId )->where( 'post_id', $postId )->get()->all();

    return self::supercharge( $reports );
  }

  static function supercharge( &$reports )
  {
    if ( !is_array( $reports ) ) {
      $reports = [$reports];
    }

    foreach( $reports as &$report ) {
      if ( $user = $report->user()->first() ) {
        $report->setAttribute( 'user', $user );
      }
      if ( $post = $report->post()->first() ) {
        PostRepository::supercharge( $post );
        $report->setAttribute( 'post', $post[0] );
      }

      $report->setAttribute( 'post_report_count', AbuseReport::where( 'post_id', '=', $report->post_id )->count() );
    }

    return $reports;
  }

}