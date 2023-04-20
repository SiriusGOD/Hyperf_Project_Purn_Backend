@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            @if(authPermission('video-create'))
                                <div class="col-sm-12 col-md-12" style="display:none;">
                                    <a class="btn badge-info"
                                       href="/admin/video/create">{{trans('default.video.create') ?? '新增影片'}}</a>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <form action="/admin/video/index" method="get" class="col-md-12">
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>{{trans('default.video.title') ?? '影片名稱'}}</label>
                                            <input type="text" class="form-control" name="title" aria-describedby="title" value="{{$title ?? ''}}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>{{trans('default.video.status') ?? '狀態'}}</label>
                                            <select name="status" class="form-control">
                                                <option value=""></option>
                                                @foreach(trans('default.video.status_type') as $key => $value)
                                                    <option value="{{$key}}" @if($key == $status) selected @endif>{{$value}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>{{trans('default.video.start_duration') ?? '最小時長'}}</label>
                                            <input type="text" class="form-control" name="start_duration" aria-describedby="title" value="{{$start_duration ?? ''}}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>{{trans('default.video.end_duration') ?? '最大時長'}}</label>
                                            <input type="text" class="form-control" name="end_duration" aria-describedby="title" value="{{$end_duration ?? ''}}">
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
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.title') ?? '影片名稱'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.category') ?? '分類'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.is_free') ?? '是否限免'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.click') ?? '觀看次數'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.like') ?? '點讚數'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.pay_type') ?? '影片付費方式'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.hot_order') ?? '大家都在看排序'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.video.is_hide') ?? '隐藏'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="CSS grade: activate to sort column ascending">{{trans('default.action') ?? '動作'}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $video)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $video->id}}</td>
                                            <td> {{ $video->title}}</td>
                                            <td> {{ $const::CATEGORY[$video->category]}}</td>
                                            <td> {{ $const::IS_FREE[$video->is_free]}}</td>
                                            <td> {{ $video->click_count ?? 0 }}</td>
                                            <td> {{ $video->like_count ?? 0 }}</td>
                                            <td>{{ trans('default.image_group_control.image_group_pay_type_types')[$video->pay_type] }}</td>
                                            <td> {{ $video->hot_order ?? 0 }}</td>
                                            <td> {{ $const::IS_HIDE[$video->is_hide]}}</td>
                                            <td>
                                                @if(authPermission('video-edit'))
                                                    <a href="/admin/video/edit?id={{$video->id}}"
                                                       class="btn btn-primary">{{trans('default.edit') ?? '編輯'}}</a>
                                                @endif


                                                @if(authPermission('video-delete'))
                                                    <form action="/admin/video/delete" method="post" _method="delete">
                                                        <input type="hidden" name="_method" value="delete">
                                                        <input type="hidden" name="id" value="{{$video->id}}">
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
                                        <th rowspan="1" colspan="1">{{trans('default.video.title') ?? '影片名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.category')?? '分類'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.is_free') ?? '是否限免'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.click') ?? '觀看次數'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.like') ?? '點讚數'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.pay_type') ?? '影片付費方式'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.hot_order') ?? '大家都在看排序'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.video.is_hide') ?? '隐藏'}}</th>
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
