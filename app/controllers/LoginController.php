<?php

class LoginController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('login.index');
    }

    /**
     * Registration form.
     *
     * @return Response
     */
    public function register()
    {
        return View::make('login.register');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('login.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $rules = array(
            'email' => 'required|email|unique:users,email',
            'password' => 'required|alpha_num|between:4,50',
            'username' => 'required|alpha_num|between:2,20|unique:users,username'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        $user = new User;
        $user->email = Input::get('email');
        $user->username = Input::get('username');
        $user->password = Hash::make(Input::get('password'));
        $user->save();

        Mail::send('emails.welcome', array('username' => $user->username), function ($message) use ($user) {
            $message->to($user->email, $user->username)->subject(trans('login.welcome_subj'));
        });

        Auth::loginUsingId($user->id);

        return Redirect::home()->with('message', trans('login.thank_you'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return View::make('login.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        return View::make('login.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    public function dashboard()
    {
        return View::make('login.dashboard');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Log in to site.
     *
     * @return Response
     */
    public function login()
    {
        if (Auth::attempt(array('email' => Input::get('email'), 'password' => Input::get('password')), true)
            || Auth::attempt(array('username' => Input::get('email'), 'password' => Input::get('password')), true)
        ) {

            if (!Auth::user()->isRegular()) {
                return Redirect::to('dashboard');
            }

            return Redirect::intended('/');
        }

        return Redirect::back()->withInput(Input::except('password'))->with('message', trans('login.wrong_creds'));
    }


    /**
     * Log out from site.
     *
     * @return Response
     */
    public function logout()
    {
        Auth::logout();

        return Redirect::to('login')->with('message', trans('login.see_again'));
    }

    /**
     * Show reminder form.
     *
     * @return Response
     */
    public function showReminderForm()
    {
        return View::make('auth.remind');
    }


    /**
     * Send reminder email.
     *
     * @return Response
     */
    public function sendReminder()
    {
        $result = Password::remind(Input::only('email'), function ($message, $user) {
            $message->subject(trans('login.reminder_subject'));
        });

        switch ($result) {
            case Password::INVALID_USER:
                return Redirect::back()->with('error', trans('login.wrong_creds'));

            case Password::REMINDER_SENT:
                return Redirect::back()->with('status', trans('login.reminder_sent'));
        }

    }


    /**
     * Show reset password form.
     *
     * @return Response
     */
    public function showResetForm($token)
    {
        if (is_null($token)) App::abort(404);

        return View::make('auth.reset')->with('token', $token);
    }


    /**
     * Reset password.
     *
     * @return Response
     */
    public function resetPassword($token)
    {
        $credentials = array('email' => Input::only('email'));

        $response = Password::reset($credentials, function ($user, $password) {
            $user->password = Hash::make($password);

            $user->save();

            Auth::loginUsingId($user->id);

        });

        switch ($response) {
            case Password::INVALID_PASSWORD:
            case Password::INVALID_TOKEN:
            case Password::INVALID_USER:
                return Redirect::back()->with('error', trans('login.success_reset'));

            case Password::PASSWORD_RESET:
                return Redirect::home()->with('message', trans('login.wrong_creds'));
        }
    }

}
