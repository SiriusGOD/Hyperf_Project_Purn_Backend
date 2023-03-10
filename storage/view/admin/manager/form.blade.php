@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/manager/store" method="post" class="col-md-12">
                                @if($user->id)
                                    <input type="hidden" name="id" value="{{$user->id}}">
                                @endif
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.manager_control.manager_acc') ?? '管理者帳號'}}</label>
                                    <input type="text" class="form-control" name="name" id="name"
                                           @if(isset($user->name)) disabled="true" @endif
                                           placeholder="{{trans('default.account_def') ?? 'name'}}"
                                           value="{{$user->name}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.manager_control.manager_pass') ?? '密碼'}}</label>
                                    <input type="password" class="form-control" name="password" id="password"
                                           placeholder="{{trans('default.pass_def') ?? 'password'}}" value="">
                                </div>
                                @if($qrcode_image && env('GOOGLE_AUTH_VALID') && env('GOOGLE_AUTH_VALID'))
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">{{trans('default.manager_control.GoogleAtuh') ?? 'Google Auth'}}</label>
                                        <img src="data:image/png;base64, {{$qrcode_image}} "/>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.role_control.role') ?? '角色'}}</label>
                                    <select class="form-control" name="role_id">
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}" @if(isset($user))
                                                {{($user->role_id==$role->id)?"selected=true":"" }}
                                                    @endif >{{$role->name}}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <button type="submit"
                                        class="btn btn-primary">{{trans('default.submit') ?? '送出'}}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->


        </div>
        <!-- /.col -->
    </div>

@endsection