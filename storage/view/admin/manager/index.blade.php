@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            @if(authPermission('manager-create'))
                                <div class="col-sm-12 col-md-12">
                                    <a class="btn badge-info"
                                       href="/admin/manager/create">{{trans('default.manager_control.manager_insert') ?? '新增管理者'}}</a>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="example2" class="table table-bordered table-hover dataTable dtr-inline"
                                       aria-describedby="example2_info">
                                    <thead>
                                    <tr>
                                        <th class="sorting sorting_asc" tabindex="0" aria-controls="example2"
                                            rowspan="1"
                                            colspan="1" aria-sort="ascending"
                                            aria-label="Rendering engine: activate to sort column descending">{{trans('default.id') ?? '序號'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.account') ?? '帳號'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.role') ?? '角色 '}}
                                        </th>
                                        @if(env('GOOGLE_AUTH_VALID'))
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.googleAuth') ?? '開啟GOOGLE AUTH驗證 '}}
                                        </th>
                                        @endif
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.enable_user') ?? '啟用'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="CSS grade: activate to sort column ascending">{{trans('default.action') ?? '動作'}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $user)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $user->id}}</td>
                                            <td>{{ $user->name}}</td>
                                            <td>
                                                {{ isset($roles[$user->role_id-1])?$roles[$user->role_id-1]['name']:""}} </td>

                                            @if(env('GOOGLE_AUTH_VALID'))
                                                <td>{!!  strlen($user->avatar)>3 ?  "<b >己啟用</b>" : "<b color=red>未啟用</b>"  !!}</td>
                                            @endif
                                            <td>{{ $user->status}}</td>
                                            <td>
                                                @if(authPermission('manager-edit'))
                                                    <a href="/admin/manager/edit?id={{$user->id}}"
                                                       class="btn btn-primary">{{trans('default.edit') ?? '編輯'}}</a>
                                                @endif

                                                @if(authPermission('manager-google-auth') && strlen($user->avatar)==0 && env('GOOGLE_AUTH_VALID'))
                                                    <a href="/admin/manager/googleAuth?id={{$user->id}}"  class="btn btn-info">{{trans('default.googleAuth') ?? '設定google auth'}}</a>
                                                @endif

                                                @if(authPermission('manager-delete'))
                                                    <form action="/admin/manager/delete" method="post" _method="delete">
                                                        <input type="hidden" name="_method" value="delete">
                                                        <input type="hidden" name="id" value="{{$user->id}}">
                                                        <input type="submit" class="btn btn-danger"
                                                               value="{{trans('default.delete') ?? '刪除'}}">
                                                    </form>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.account') ?? '帳號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.enable_user')?? '啟用'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.action') ?? '動作'}}</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="example2_info" role="status" aria-live="polite">
                                    {{trans('default.table_page_info',[
                                            'page' => $page,
                                            'total' => $total,
                                            'last_page' => $last_page,
                                            'step' => $step,
                                        ]) ?? '顯示第 $page 頁
                                        共 $total 筆
                                        共 $last_page 頁
                                        每頁顯示 $step 筆'}}
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous {{$page <= 1 ? 'disabled' : ''}}"
                                            id="example2_previous">
                                            <a href="{{$prev}}"
                                               aria-controls="example2" data-dt-idx="0" tabindex="0"
                                               class="page-link">{{trans('default.pre_page') ?? '上一頁'}}</a>
                                        </li>
                                        <li class="paginate_button page-item next {{$last_page <= $page ? 'disabled' : ''}}"
                                            id="example2_next">
                                            <a href="{{$next}}"
                                               aria-controls="example2"
                                               data-dt-idx="7"
                                               tabindex="0"
                                               class="page-link">{{trans('default.next_page') ?? '下一頁'}}</a>
                                        </li>
                                    </ul>


                                </div>
                            </div>
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