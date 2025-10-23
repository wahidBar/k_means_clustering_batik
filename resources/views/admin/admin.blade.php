@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Create User</h2>
  <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <input name="name" class="form-control" placeholder="Name" required>
    <input name="email" class="form-control" placeholder="Email" required>
    <input name="password" type="password" class="form-control" placeholder="Password" required>
    <input name="password_confirmation" type="password" class="form-control" placeholder="Confirm Password" required>
    <select name="role_id" class="form-control" required>
      @foreach($roles as $r)
        <option value="{{ $r->id }}">{{ $r->name }}</option>
      @endforeach
    </select>
    <button class="btn btn-success mt-2">Create</button>
  </form>
</div>
@endsection
