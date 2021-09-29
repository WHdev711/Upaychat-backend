<?php

namespace App\Http\Controllers\API;

use App\Helper\Helper;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function forgotpassword(Request $request)
    {
        $mobile = $request->mobile_no;

        $user = User::firstWhere('mobile', $mobile);
        if ($user != null) {
            $user->password = Hash::make($request->password);
            $user->save();
            ////////////////////////////////////
            $response['status'] = "true";
            $response['message'] = 'Password changed successfully';
            $response['data'] = [];
            return response()->json($response);
        } else {
            $response['status'] = "false";
            $response['message'] = 'No user found with mobile no ' . $mobile;
            $response['data'] = [];
            return response()->json($response);
        }
    }

    public function usersearch(Request $request)
    {
        $user = Auth::user();
        $extralist = array();
        if (isset($request['roll']) && $request['roll'] != '')
            $userlist = User::select('id', 'avatar', 'firstname', 'lastname', 'username', 'email', 'mobile')
                ->where([/*['roll_id', 3], */ ['id', '!=', $user->id], ['roll', $request['roll']]])->get();
        else {
            $ids = Transaction::select("touser_id as id")
                ->where('user_id', $user->id)
                ->where('touser_id', '!=', $user->id);
            $uids = Transaction::select("user_id as id")
                ->where('touser_id', $user->id)
                ->where('user_id', '!=', $user->id)
                ->union($ids)
                ->get();

            $userlist = User::select('id', 'avatar', 'firstname', 'lastname', 'username', 'email', 'mobile')
                ->whereIn('id', $uids->pluck('id'))
                ->where([/*['roll_id', 3], */ ['id', '!=', $user->id]])->get();

            $extralist = User::select('id', 'avatar', 'firstname', 'lastname', 'username', 'email', 'mobile')
                ->whereNotIn('id', $uids->pluck('id'))
                ->where([/*['roll_id', 3], */ ['id', '!=', $user->id]])->get();
        }

        $ulist = array();
        foreach ($userlist as $u)
            $ulist[] = array(
                'user_id' => $u->id,
                'firstname' => $u->firstname,
                'lastname' => $u->lastname,
                'username' => $u->username,
                'email' => $u->email,
                'mobile' => $u->mobile,
                'profile_image' => $u->avatar,
                'extra' => false,
            );
        foreach ($extralist as $u)
            $ulist[] = array(
                'user_id' => $u->id,
                'firstname' => $u->firstname,
                'lastname' => $u->lastname,
                'username' => $u->username,
                'email' => $u->email,
                'mobile' => $u->mobile,
                'profile_image' => $u->avatar,
                'extra' => true,
            );
        $response['status'] = "true";
        $response['message'] = 'Search found ' . count($ulist) . ' records ';
        $response['data'] = $ulist;
        return response()->json($response);
    }

    public function deletepic(Request $request)
    {
        $user = Auth::user();
        try {
            $user->clearMediaCollection('profile_image');
            $response['status'] = "true";
            $response['message'] = " Image removed successfully";
            $response['data'] = '';
            return response()->json($response);
        } catch (\Exception $e) {
            $response['status'] = "false";
            $response['message'] = " Image could not be removed";
            $response['data'] = '';
            return response()->json($response);
        }
    }

    public function checkemail(Request $request)
    {
        // print(json($response));
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
        ]);
        if ($validator->fails()) {
            $errmess = implode(', ', $validator->messages()->all());
            $response['status'] = "false";
            $response['message'] = $errmess;
            $response['data'] = [];
            return response()->json($response);
        } else {
            $response['status'] = "true";
            return response()->json($response);
        }
    }

    public function checkmobile(Request $request)
    {
        if ($request->exist == 'false') {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|unique:users',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|exists:users',
            ]);
        }
        if ($validator->fails()) {
            $errmess = implode(', ', $validator->messages()->all());
            $response['status'] = "false";
            $response['message'] = $errmess;
        } else {
            $code = Helper::generateRandomNumber();
            Helper::sendSMS($request->mobile, "Your UpayChat Code is " . $code.". It expires in 5 minutes.");
            $response['status'] = "true";
            $response['message'] = $code;
        }
        $response['data'] = [];
        return response()->json($response);
    }

    public function register(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader != 'application/json') {
            return response()->json(array('error' => 'Wrong custom header'), 400);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'username' => 'required|unique:users',
            'mobile' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'birth' => 'required',
            'fcm_token' => 'required',
        ]);
        if ($validator->fails()) {
            $errmess = implode(', ', $validator->messages()->all());
            $response['status'] = "false";
            $response['message'] = $errmess;
            return response()->json($response);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        if ($request->has('profile_image')) {
            $date = Str::slug(Carbon::now());
            $imageName = $user->id . '-' . $date;
            Image::make($request->file('profile_image'))->save(public_path('/uploads/users/') . $imageName . '.jpg')->encode('jpg', '50');
            $user->avatar = '/uploads/users/' . $imageName . '.jpg';
        }
        $user->save();

        $uid = $user->id;
        $wallet = Wallet::create([
            'user_id' => $uid,
            'balance' => '0.00',
        ]);

        $pendings = Transaction::where([['touser_id', $request->email], ['status', 4]])
            ->orWhere([['touser_id', $request->mobile], ['status', 4]])->get();

        foreach ($pendings as $pending) {
            $pending->touser_id = $uid;

            $sender = User::find($pending->user_id);

            if (strtolower($pending->transaction_type) == 'pay') {
                $pending->status = 1;
                // update this user's wallet
                $wallet->balance += $pending->amount;

                $message = $sender->firstname . " " . $sender->lastname . " paid you ₦" . number_format($pending->amount, 2, '.', ',');
            } elseif (strtolower($request->transaction_type) == 'request') {
                $pending->status = 0;
                $message = $sender->firstname . " " . $sender->lastname . " requested ₦" . number_format($pending->amount, 2, '.', ',') . " from you";
            }
            $pending->save();

            ////////////////////// notification for receiver ///////////////
            $Noti = new Notification;
            $Noti->post_id = $pending->id;
            $Noti->user_id = $pending->touser_id;
            $Noti->notification = $message;
            $Noti->save();
        }
        $wallet->save();

        $user = User::find($uid);

        $userdata = array(
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'username' => $user->username,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'roll' => $user->roll,
            'profile_image' => $user->avatar,
        );

        $response['status'] = "true";
        $response['message'] = "Successfully registered.";
        $response['token'] = $user->createToken(config('vms.myToken'))->accessToken;
        $response['data'] = $userdata;

        return response()->json($response);
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::user()->AauthAcessToken()->delete();
        }
    }

    public function login(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        if ($acceptHeader != 'application/json') {
            $response['status'] = "false";
            $response['message'] = "Not a valid API request.";
            $response['token'] = "";
            $response['data'] = [];
            return response()->json($response);
        }
        if (!($request->login_user) || (!$request->password)) {
            $response['status'] = "false";
            $response['message'] = 'Missing params';
            $response['data'] = [];
            return response()->json($response);
        }
        $login_type = filter_var($request->input('login_user'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$login_type => request('login_user'), 'password' => request('password')]) ||
            ($login_type == 'username' &&
                (Auth::attempt(['mobile' => request('login_user'), 'password' => request('password')]) ||
                    Auth::attempt(['mobile' => '+' . request('login_user'), 'password' => request('password')])))) {
            $user = Auth::user();
            $user->fcm_token = $request->fcm_token;
            $user->save();

            $userdata = array(
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'username' => $user->username,
                'birth' => $user->birth,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'roll' => $user->roll,
                'profile_image' => $user->avatar,
            );

            $response['status'] = "true";
            $response['message'] = "Successfully logged in.";
            $response['token'] = $user->createToken(config('vms.myToken'))->accessToken;
            $response['data'] = $userdata;
        } else {
            $response['status'] = "false";
            $response['message'] = "Invalid username or password.";
            $response['token'] = "";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function updateprofile(Request $request)
    {
        $data = $request->all();
        $acceptHeader = $request->header('Accept');

        if ($acceptHeader != 'application/json') {
            return response()->json(array('error' => 'Wrong custom header'), 400);
        }
        $user = Auth::user();

        try {
            $user->firstname = $data['firstname'];
            $user->lastname = $data['lastname'];
            $user->birth = $data['birth'];

            if ($request->has('profile_image')) {
                File::delete(public_path($user->avatar));
                $date = Str::slug(Carbon::now());
                $imageName = $user->id . '-' . $date;
                Image::make($request->file('profile_image'))->save(public_path('/uploads/users/') . $imageName . '.jpg')->encode('jpg', '50');
                $user->avatar = '/uploads/users/' . $imageName . '.jpg';
            }
            $user->save();

            $response['status'] = "true";
            $response['message'] = "Profile updated successfully.";
            $response['firstname'] = $request->input('firstname');
            $response['lastname'] = $request->input('lastname');
            $response['birth'] = $request->input('birth');
            $response['profile_image'] = $user->avatar;

            return response()->json($response);
        } catch (\Exception $e) {
            $response['status'] = "false";
            $response['message'] = $e->getMessage();//"System Error ";
            $response['data'] = '';
            return response()->json($response);
        }
    }

    public function changepassword(Request $request)
    {
        if (!($request->new_password) || (!$request->password)) {
            $response['status'] = "false";
            $response['message'] = "Missing param";
            $response['token'] = "";
            $response['data'] = '';
            return response()->json($response);
        }
        $user = Auth::user();

        //param1 - user password that has been entered on the form
        //param2 - old password hash stored in database
        if (Hash::check($request->password, $user->password)) {
            $newpass = Hash::make($request->new_password);

            $user->password = $newpass;
            $user->save();

            $response['status'] = "true";
            $response['message'] = "Password changed successfully";
            $response['data'] = '';
            return response()->json($response);
        } else {
            $response['status'] = "false";
            $response['message'] = "Invalid current password";
            $response['data'] = [];
            return response()->json($response);
        }
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user]);
    }
}
