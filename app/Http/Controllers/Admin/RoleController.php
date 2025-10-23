<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;


/**
 * @method void middleware($middleware, array $options = [])
 */

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin']);
    }

    public function index()
    {
        $roles = Role::paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function create() { return view('admin.roles.create'); }

    public function store(Request $request)
    {
        $data = $request->validate(['name'=>'required|alpha_dash|unique:roles,name','description'=>'nullable']);
        Role::create($data);
        return redirect()->route('admin.roles.index')->with('success','Role created.');
    }

    public function edit(Role $role) { return view('admin.roles.edit', compact('role')); }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate(['name'=>'required|alpha_dash|unique:roles,name,'.$role->id,'description'=>'nullable']);
        $role->update($data);
        return redirect()->route('admin.roles.index')->with('success','Role updated.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success','Role deleted.');
    }
}
