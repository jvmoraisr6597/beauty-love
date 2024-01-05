<?php


namespace App\Http\Controllers;

use App\User;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Hash;
use App\Permission;
use App\Role;
use App\Http\Resources\Clients as ClientsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    /**
     * Display a listing of the users
     *
     * @param  \App\User  $model
     * @return \Illuminate\View\View
     */
    public function index(User $model)
    {
        return view('users.index', ['users' => $model->paginate(15)]);
    }

    /**
     * Show the form for creating a new user
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::pluck('name', 'id');
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @param  \App\User  $model
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request, User $model)
    {
        // Create the user
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Gere um nome único para a imagem
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // Mova a imagem para o diretório de armazenamento (geralmente 'public/storage')
            $image->storeAs('public', $imageName);
            
            // Salve os detalhes da imagem no banco de dados (se você tiver uma tabela de imagens)
            // Exemplo usando um modelo chamado Image:
            $request['image'] = storage_path('app/public/' . $imageName);
        }
        if ($user = $model->create($request->merge(['password' => Hash::make($request->get('password'))])->all())) {
            $this->syncPermissions($request, $user);
        } else {
            return redirect()->route('user.index')->withError('Unable to create user.');
        }

        return redirect()->route('user.index')->withStatus(__('User successfully created.'));
    }

    /**
     * Show the form for editing the specified user
     *
     * @param  \App\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'id');
        $permissions = Permission::all('name', 'id');
        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Update the specified user in storage
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, User  $user)
    {
        $user->update(
            $request->merge(['password' => Hash::make($request->get('password'))])
            ->except([$request->get('password') ? '' : 'password'])
        );
        // Handle the user roles
        $this->syncPermissions($request, $user);

        return redirect()->route('user.index')->withStatus(__('User successfully updated.'));
    }

    /**
     * Remove the specified user from storage
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User  $user)
    {
        $user->delete();

        return redirect()->route('user.index')->withStatus(__('User successfully deleted.'));
    }

    private function syncPermissions(UserRequest $request, $user)
    {
        // Get the submitted roles
        $roles = $request->get('roles', []);
        $permissions = $request->get('permissions', []);

        // Get the roles
        $roles = Role::find($roles);

        // check for current role changes
        if (! $user->hasAllRoles($roles)) {
            // reset all direct permissions for user
            $user->permissions()->sync([]);
        } else {
            // handle permissions
            $user->syncPermissions($permissions);
        }

        $user->syncRoles($roles);
        return $user;
    }


    public function showApi(User $user) {
        return new ClientsResource($user);
    }

    public function getClientsJson() {
        return ClientsResource::collection(User::all());
    }

    public function getClients(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        
        $query = User::eloquentQuery($orderBy, $orderByDir, $searchValue);
        $data = $query->paginate($length);
        
        return new DataTableCollectionResource($data);
    }

    public function storeApi(UserRequest $request)
    {
        // Create the user
        if (!User::create($request->merge(['password' => Hash::make($request->get('password'))])->all())) {
            return json_encode([
                "code" => 402,
                "message" => "Unable to create user"
            ]);
        }

        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }

    public function deleteApi(User $user) {
        return $user->delete();
    }

    public function updateApi(Request $request, User $user)
    {
        $request = $request->all();

        unset($request['password']);
        unset($request['password_confirmation']);

        $user->update($request);

        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }

    public function updatePasswordApi(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:4|confirmed',
        ]);
    
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Senha atual incorreta'],
            ]);
        }

        // Atualize a senha do usuário
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }

    public function updatePasswordFirstStep(Request $request)
    {
        $email = $request->all()['email'];
        $rand = rand(100000, 999999);
        Redis::sadd($email, $rand);

        print(Redis::spop($email));

        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }
}
