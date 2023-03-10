@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/role/store" method="post" class="col-md-12">
                                @if($role->id)
                                    <input type="hidden" name="id" value="{{$role->id}}">
                                @endif
                                <div class="form-group">
                                    <label for="exampleInputEmail1">
                                        {{trans('default.role_control.role_name') ?? '角色名稱'}}
                                    </label>
                                    <input type="text" class="form-control" name="name" id="name"
                                           placeholder="{{trans('default.account_def') ?? 'name'}}"
                                           value="{{$role->name}}">
                                </div>

                                <div class="form-group">
                                    <div class="col-md-12">
                                        <b>
                                            <lable>
                                                {{trans('default.role_control.role_permission') ?? '權限'}}
                                                <input type="checkbox" class="checkbox_area" id="select_all"
                                                       data-id="all"> 全選
                                            </lable>

                                        </b>
                                        <hr>
                                        @foreach($permissions as $key => $mains)
                                            <label>
                                                {{__("default.titles.$key")}}
                                                <input type="checkbox" class="checkbox_area" id="main_{{$key}}"
                                                       data-id="{{$key}}">
                                            </label>
                                            <br>
                                            @foreach($mains as $permission)

                                                <label style="margin-right: 8px;
                                                    @if(env('GOOGLE_AUTH_VALID')==0  && $permission["name"] !='manager-googleAuth')  display:none;  @endif
                                                 " >
                                                    {{cutStrLang($permission["name"])}}
                                                    <input type="checkbox" name="permissions[]"
                                                           class="area_{{$key}}"
                                                           value="{{$permission["id"]}}"
                                                            {{checkInAryRtnStr(intval($permission["id"]), $rolePermission , "checked")}}>
                                                </label>

                                            @endforeach
                                            <hr>
                                        @endforeach
                                    </div>
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
        $(function () {
            $("input.checkbox_area").bind('click', function () {
                //全選
                if ($(this).data("id") === 'all') {
                    $("input[type='checkbox']").each(function () {
                        if ($("#select_all").prop('checked')) {
                            $(this).prop('checked', true);
                        } else {
                            $(this).prop('checked', false);
                        }
                    });
                } else {
                    //區域全選
                    if ($(this).prop('checked')) {
                        $(".area_" + $(this).data("id")).prop('checked', true);
                    } else {
                        $(".area_" + $(this).data("id")).prop('checked', false);
                    }
                }
            });
        });
    </script>
@endsection