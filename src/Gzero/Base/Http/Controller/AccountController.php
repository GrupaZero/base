<?php namespace Gzero\Base\Http\Controller;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountController extends Controller {

    public function index()
    {
        return view('gzero-base::account.index');
    }

    public function edit(Request $request)
    {
        return view('gzero-base::account.edit', ['isUserEmailSet' => strpos($request->user()->email, '@')]);
    }

    public function oauth()
    {
        return view('gzero-base::account.oauth');
    }

    /**
     * Show welcome page for registered user.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function welcome(Request $request)
    {
        if (session()->has('showWelcomePage')) {
            session()->forget('showWelcomePage');

            return view('gzero-base::account.welcome', ['method' => $request->get('method')]);
        }

        return redirect()->route('home');
    }
}
