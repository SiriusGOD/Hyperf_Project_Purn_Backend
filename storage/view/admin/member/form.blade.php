@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/member/store" method="post" class="col-md-12">
                                @if($user->id)
                                    <input type="hidden" name="id" value="{{$user->id}}">
                                @endif
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_acc') ?? '管理者帳號'}}</label>
                                    <input type="text" class="form-control" name="name" id="name"
                                           @if(!empty($user->name)) disabled="true" @endif
                                           placeholder="{{trans('default.account_def') ?? 'name'}}"
                                           value="{{$user->name}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_pass') ?? '密碼'}}</label>
                                    <input type="password" class="form-control" name="password" id="password"
                                           placeholder="{{trans('default.pass_def') ?? 'password'}}" value="">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_sex') ?? '性別'}}</label>
                                    <select class="form-control form-control-lg" name="sex">
                                        @foreach(trans('select.sex') as $key => $value)
                                            <option value="{{$key}}" @if($key == $user->sex) selected=true @endif>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_age') ?? '年齡'}}</label>
                                    <input type="text" class="form-control" name="age" id="age"
                                           value="{{$user->age}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.image_profile_dec') ?? '圖片(不上傳就不更新，只接受圖片檔案(png jpeg gif))'}}</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="avatar" id="customFile" accept="image/png, image/gif, image/jpeg">
                                        <label class="custom-file-label" for="customFile">{{trans('default.choose_file') ?? '選擇檔案'}}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_email') ?? '電子郵件'}}</label>
                                    <input type="text" class="form-control" name="email" id="email"
                                           value="{{$user->email}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_phone') ?? '電子郵件'}}</label>
                                    <input type="text" class="form-control" name="phone" id="phone"
                                           value="{{$user->phone}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_status') ?? '狀態'}}</label>
                                    <select class="form-control form-control-lg" name="status">
                                        @foreach(trans('select.status') as $key => $value)
                                            <option value="{{$key}}" @if($key == $user->status) selected=true @endif>{{$value}}</option>
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