@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            @if(authPermission('advertisement-create'))
                            <div class="col-sm-12 col-md-12 mb-1">
                                <a class="btn badge-info" href="/admin/advertisement/create">{{trans('default.ad_control.ad_insert') ?? '新增廣告'}}</a>
                                <label>{{trans('default.take_up_down_msg') ?? '上架需在有效時間內才有用，下架是任意時間都有用'}}</label>
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
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.name') ?? '名稱'}}
                                        </th>

                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.image') ?? '圖片'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.ad_control.ad_connect_url') ?? '連結網址'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.ad_control.ad_place') ?? '廣告位置'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.start_time')?? '開始時間'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.end_time') ?? '結束時間'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.buyer') ?? '購買人'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.take_msg') ?? '上下架'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Engine version: activate to sort column ascending">
                                            {{trans('default.attribution_web')?? '歸屬網站'}}
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
                                            <td class="sorting_1 dtr-control">{{ $model->id}}</td>
                                            <td>{{ $model->name}}</td>
                                            <td>
                                                <img src="{{$model->image_url}}" alt="image" style="width:100px">
                                            </td>
                                            <td>{{ $model->url}}</td>
                                            <td>
                                                @switch($model->position)
                                                    @case(\App\Model\Advertisement::POSITION['top_banner'])
                                                        上 banner
                                                        @break

                                                    @case(\App\Model\Advertisement::POSITION['bottom_banner'])
                                                        下 banner
                                                        @break

                                                    @case(\App\Model\Advertisement::POSITION['popup_window'])
                                                        彈窗
                                                        @break

                                                    @case(\App\Model\Advertisement::POSITION['ad_image'])
                                                        圖片廣告
                                                        @break

                                                    @case(\App\Model\Advertisement::POSITION['ad_link'])
                                                        友情鏈接
                                                        @break
                                                
                                                    @default
                                                        彈窗
                                                @endswitch</td>
                                            <td>{{ $model->start_time}}</td>
                                            <td>{{ $model->end_time}}</td>
                                            <td>{{ $model->buyer}}</td>
                                            <td>{{ $model->expire == 0 ? trans('default.take_up') : trans('default.take_down')}}</td>
                                            <td>{{ $model->site->name ?? trans('default.unattribution_web')}}</td>
                                            <td>
                                                @if(authPermission('advertisement-edit'))
                                                    <div class="row mb-1">
                                                    <a href="/admin/advertisement/edit?id={{$model->id}}" class="btn btn-primary">{{trans('default.edit') ?? '編輯'}}</a>
                                                    </div>
                                                @endif
                                                @if(authPermission('advertisement-expire'))
                                                <div class="row mb-1">
                                                    <form action="/admin/advertisement/expire" method="post">
                                                        <input type="hidden" name="id" value="{{$model->id}}" >
                                                        <input type="hidden" name="expire" value="{{\App\Model\Advertisement::EXPIRE['yes']}}" >
                                                        <input type="submit"  class="btn btn-danger" value="{{trans('default.take_down') ?? '下架'}}">
                                                    </form>
                                                </div>


                                                <div class="row mb-1">
                                                    <form action="/admin/advertisement/expire" method="post">
                                                        <input type="hidden" name="id" value="{{$model->id}}" >
                                                        <input type="hidden" name="expire" value="{{\App\Model\Advertisement::EXPIRE['no']}}" >
                                                        <input type="submit"  class="btn btn-danger" value="{{trans('default.take_up') ?? '上架'}}">
                                                    </form>
                                                </div>
                                                @endif

                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.name')?? '名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.image') ?? '圖片'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.ad_control.ad_connect_url') ?? '連結網址'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.ad_control.ad_place') ?? '廣告位置'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.start_time') ?? '開始時間'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.end_time') ?? '結束時間'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.buyer') ?? '購買人'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.take_msg')?? '上下架'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.attribution_web')?? '歸屬網站'}}</th>
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
                                        <li class="paginate_button page-item previous {{$page <= 1 ? 'disabled' : ''}}" id="example2_previous">
                                            <a href="{{$prev}}"
                                               aria-controls="example2" data-dt-idx="0" tabindex="0"
                                               class="page-link">{{trans('default.pre_page') ?? '上一頁'}}</a>
                                        </li>
                                        <li class="paginate_button page-item next {{$last_page <= $page ? 'disabled' : ''}}" id="example2_next">
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