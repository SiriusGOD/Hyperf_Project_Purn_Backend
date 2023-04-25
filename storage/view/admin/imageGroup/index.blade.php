@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">

                            @if(authPermission('image-group-create'))
                                <div class="col-sm-12 col-md-12 mb-1">
                                    <a class="btn badge-info" href="/admin/image_group/create">{{trans('default.image_group_control.image_group_insert') ?? '新增套圖'}}</a>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <form action="/admin/image_group/index" method="get" class="col-md-12">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>{{trans('default.image_group_control.image_group_name') ?? '套圖名稱'}}</label>
                                        <input type="text" class="form-control" name="title" aria-describedby="title" value="{{$title ?? ''}}">
                                    </div>
                                </div>
                                @include('partial.tagSelect')
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">search</button>
                                </div>
                            </form>
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
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_name') ?? '套圖名稱'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_thumbnail') ?? '套圖縮圖'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_url') ?? '套圖網址'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_clicks') ?? '套圖觀看次數'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_likes') ?? '套圖點讚數'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_pay_type') ?? '套圖付費方式'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.image_group_control.image_group_hot_order') ?? '大家都在看排序'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="CSS grade: activate to sort column ascending">{{trans('default.action') ?? '動作'}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $model)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $model->id }}</td>
                                            <td>{{ $model->user->name }}</td>
                                            <td>{{ $model->title }}</td>
                                            <td>
                                                <img src="{{ $model->getAdminBaseUrl() . $model->thumbnail }}" alt="">
                                            </td>
                                            <td>
                                                <a href="{{ $model->getAdminBaseUrl() . $model->url }}" target="_blank">link</a>
                                            </td>
                                            <td>{{ $model->click_count ?? 0 }}</td>
                                            <td>{{ $model->like_count ?? 0 }}</td>
                                            <td>{{ trans('default.image_group_control.image_group_pay_type_types')[$model->pay_type] }}</td>
                                            <td>{{ $model->hot_order ?? 0 }}</td>
                                            <td>
                                                @if(authPermission('image-group-edit'))
                                                    <div class="row mb-1">
                                                        <a href="/admin/image_group/edit?id={{$model->id}}" class="btn btn-primary">{{trans('default.edit') ?? '編輯'}}</a>
                                                    </div>
                                                @endif
                                                @if(authPermission('image-group-delete'))
                                                    <div class="row mb-1">
                                                        <a href="/admin/image_group/delete?id={{$model->id}}" class="btn btn-danger">{{trans('default.delete') ?? '刪除'}}</a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.user_name') ?? '使用者名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_name') ?? '圖片名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_thumbnail') ?? '圖片縮圖'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_url') ?? '圖片網址'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_clicks') ?? '套圖觀看次數'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_likes') ?? '套圖點讚數'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_pay_type') ?? '套圖付費方式'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image_group_control.image_group_hot_order') ?? '大家都在看排序'}}</th>
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