<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;

class ExtendTokenTimer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user_tokens = PersonalAccessToken::where('tokenable_id', '=', auth()->user()->id)->get();

        foreach($user_tokens as $user_token)
        {
            if($user_token->created_at != $user_token->last_used_at)
            {
                $user_token->created_at = $user_token->last_used_at;

                $user_token->save();

                break;
            }
        }

        return $next($request);
    }
}
