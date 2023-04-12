@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">

                            @if(authPermission('tag-create'))
                                <div class="col-sm-12 col-md-12 mb-1">
                                    <a class="btn badge-info" href="/admin/tag/create">{{trans('default.tag_control.tag_insert') ?? '新增標籤'}}</a>
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
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.user_name') ?? '使用者名稱'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.tag_control.tag_name') ?? '標籤名稱'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.tag_group_control.tag_group_name') ?? '標籤群組名稱'}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $model)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $model->id }}</td>
                                            <td class="sorting_1 dtr-control">{{ $model->user->name }}</td>
                                            <td>{{ $model->name }}</td>
                                            <td>{{ $model->group_name }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.user_name') ?? '使用者名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.tag_control.tag_name') ?? '標籤名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.tag_group_control.tag_group_name') ?? '標籤群組名稱'}}</th>
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