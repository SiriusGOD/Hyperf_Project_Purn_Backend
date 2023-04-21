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
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_phone') ?? '手機'}}</label>
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
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_level') ?? '會員等級'}}</label>
                                    <select class="form-control form-control-lg" name="member_level_status">
                                        @foreach(trans('select.level') as $key => $value)
                                            <option value="{{$key}}" @if($key == $user->member_level_status) selected=true @endif>{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_level_start') ?? '會員等級起始時間'}}</label>
                                    <input type="text" class="form-control" name="start_time" placeholder="name" value="{{$user->start_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_level_end') ?? '會員等級結束時間'}}</label>
                                    <input type="text" class="form-control" name="end_time" id="end_time" placeholder="name" value="{{$user->end_time ?? \Carbon\Carbon::now()}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_coin') ?? '現金點數'}}</label>
                                    <input type="text" class="form-control" name="coins" id="coins"
                                           value="{{$user->coins}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_diamond_coins') ?? '鑽石點數'}}</label>
                                    <input type="text" class="form-control" name="diamond_coins" id="diamond_coins" value="{{$user->diamond_coins}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_diamond_quota') ?? '鑽石觀看次數'}}</label>
                                    <input type="text" class="form-control" name="diamond_quota" id="diamond_quota" value="{{$user->diamond_quota}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_vip_quota') ?? 'VIP觀看次數'}}</label>
                                    <input type="text" class="form-control" name="vip_quota" id="vip_quota" value="{{$user->vip_quota}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_free_quota') ?? '免費觀看次數'}}</label>
                                    <input type="text" class="form-control" name="free_quota" id="free_quota" value="{{$user->free_quota}}">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">{{trans('default.member_control.member_free_quota_limit') ?? '免費觀看次數上限'}}</label>
                                    <input type="text" class="form-control" name="free_quota_limit" id="free_quota_limit" value="{{$user->free_quota_limit}}">
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
    <script>
        $(function() {
            $('input[name="start_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
            $('input[name="end_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker:true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-M-DD HH:mm:00'
                }
            });
        });
    </script>
@endsection