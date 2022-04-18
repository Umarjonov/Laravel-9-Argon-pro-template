<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Rules\CurrentPasswordCheckRule;
use Illuminate\Http\Request;
use App\Services\LogWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\View\View
     */
    public function index()
    {
        abort_if (!auth()->user()->can('users.index'),403);
        $users = User::paginate();
        return view('admin.users.index',compact('users'));
    }

    // user create page
    public function create()
    {
        abort_if (!auth()->user()->can('users.create'),403);
        if (auth()->user()->hasRole('Super Admin'))
            $roles = Role::all();
        else
            $roles = Role::where('name','!=','Super Admin')->get();

        return view('admin.users.create',compact('roles'));
    }

    // user create
    public function store(Request $request)
    {
        abort_if (!auth()->user()->can('users.create'),403);
        $this->validate($request,[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $user->assignRole($request->get('roles'));

        $activity = "\nCreated by: ".json_encode(auth()->user())
            ."\nNew User: ".json_encode($user)
            ."\nRoles: ".implode(", ",$request->get('roles'));

        LogWriter::user_activity($activity,'AddingUsers');
        message_set("Muvafaqqiyatli! User qo'shildi.",'success',5);

        return redirect()->route('user.index');
    }

    // user edit page
    public function edit($id)
    {
        abort_if((!auth()->user()->can('user.edit') && auth()->user()->id != $id),403);

        $user = User::find($id);

        if ($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin'))
        {
            message_set("У вас нет разрешения на редактирование администратора",'error',5);
            return redirect()->back();
        }

        if (auth()->user()->hasRole('Super Admin'))
            $roles = Role::all();
        else
            $roles = Role::where('name','!=','Super Admin')->get();

        return view('admin.users.edit',compact('user','roles'));
    }

    // update user dates
    public function update(Request $request, $id)
    {
        abort_if((!auth()->user()->can('users.update') && auth()->user()->id != $id),403);

        $activity = "\nUpdated by: ".logObj(auth()->user());
        $this->validate($request,[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::find($id);

        if ($request->get('password') != null)
        {
            User::whereId($id)->update([
                'password' => Hash::make($request->password)
            ]);
//            $user->password = Hash::make($request->get('password'));
        }

        unset($request['password']);
        $activity .="\nBefore updates User: ".logObj($user);
        $activity .=' Roles before: "'.implode(',',$user->getRoleNames()->toArray()).'"';

        $user->fill($request->all());
        $user->save();

        if (isset($request->roles)) $user->syncRoles($request->get('roles'));
        unset($user->roles);

        $activity .="\nAfter updates User: ".logObj($user);
        $activity .=' Roles after: "'.implode(',',$user->getRoleNames()->toArray()).'"';

        LogWriter::user_activity($activity,'EditingUsers');
        message_set("Muvafaqqiyatli! User malumotlari o'zgartirildi.",'success',5);

        if (auth()->user()->can('user.edit'))
            return redirect()->route('user.index');
        else
            return redirect()->route('home');
    }

    // delete user by id
    public function destroy($id)
    {
        abort_if (!auth()->user()->can('users.destroy'),403);

        $user = User::find($id);
        if ($user->hasRole('Super Admin') && !auth()->user()->hasRole('Super Admin'))
        {
            message_set("У вас нет разрешения на редактирование администратора",'error',5);
            return redirect()->back();
        }
        DB::table('model_has_roles')->where('model_id',$id)->delete();
        DB::table('model_has_permissions')->where('model_id',$id)->delete();
        $deleted_by = logObj(auth()->user());
        $user_log = logObj(User::find($id));
        $message = "\nDeleted By: $deleted_by\nDeleted user: $user_log";
        LogWriter::user_activity($message,'DeletingUsers');
        User::destroy($id);
        message_set("User o'chirib yuborildi",'warning',5);

        return redirect()->route('user.index');
    }

    public function profileShow()
    {
        return view('admin.users.show');
    }

    public function profilePassword(Request $request)
    {
//        dd($request->old_password.'   ->   '.$request->password.'='.$request->get('password'),Hash::check($request->old_password, auth()->user()->password),auth()->user()->password,Hash::make($request->password),Hash::check($request->password, Hash::make($request->password)));
        $validator = Validator::make($request->all(), [
            'old_password'      => 'required',
            'password'      => 'required|confirmed',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        #Match The Old Password
        if(!Hash::check($request->old_password, auth()->user()->password)){
            message_set("Old Password Doesn't match!",'error',5);

            return back()->with("error", "Old Password Doesn't match!");
        }


        #Update the new Password
        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->password)
        ]);
        message_set("User parol malumoti o'zgartirildi",'success',5);

        return back()->with("status", "Password changed successfully!");
    }

}
