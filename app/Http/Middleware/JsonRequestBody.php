<?php

namespace DolphinApi\Http\Middleware;

use Closure;

class JsonRequestBody
{
  const PARSED_METHODS = [
    'POST', 'PUT', 'PATCH'
  ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
      if ( in_array($request->getMethod(), self::PARSED_METHODS ) ) {
        if ( $request->getContent() ) {
          $request->merge( json_decode( $request->getContent(), true ) );
        }
      }

      return $next( $request );
    }
}