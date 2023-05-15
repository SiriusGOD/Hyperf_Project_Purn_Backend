@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="example2_wrapper" class="dataTables_wrapper dt-bootstrap4">
                        <div class="row">
                            <form action="/admin/customer_service/reply" method="post" class="col-md-12">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <input type="hidden" name="id" value="{{ $customer_service_id }}">
                                        <label>{{trans('default.customer_service_detail_control.message') ?? '訊息'}}</label>
                                        <input type="text" class="form-control" name="message" aria-describedby="message" value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">{{ trans('default.customer_service_detail_control.message_response') }}</button>
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
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.customer_service_detail_control.member_name') ?? '會員名稱'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.customer_service_detail_control.user_name') ?? '客服名稱'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.customer_service_detail_control.message') ?? '訊息'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.customer_service_detail_control.image_url') ?? '圖片'}}
                                        </th>
                                        <th class="sorting" tabindex="0" aria-controls="example2" rowspan="1"
                                            colspan="1"
                                            aria-label="Browser: activate to sort column ascending">{{trans('default.customer_service_detail_control.created_at') ?? '建立時間'}}
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($datas as $model)
                                        <tr class="odd">
                                            <td class="sorting_1 dtr-control">{{ $model->id }}</td>
                                            <td class="sorting_1 dtr-control">{{ $model->member->name ?? null }}</td>
                                            <td class="sorting_1 dtr-control">{{ $model->user->name ?? null }}</td>
                                            <td class="sorting_1 dtr-control">{{ $model->message }}</td>
                                            <td class="sorting_1 dtr-control">
                                                @if(!empty($model->image_url))
                                                    <img src="{{\Hyperf\Support\env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live') . $model->image_url }}">
                                                @endif
                                            </td>
                                            <td class="sorting_1 dtr-control">{{ $model->created_at }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th rowspan="1" colspan="1">{{trans('default.id') ?? '序號'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.customer_service_detail_control.member_name') ?? '會員名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.customer_service_detail_control.user_name') ?? '客服名稱'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.customer_service_detail_control.message') ?? '訊息'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.customer_service_detail_control.image_url') ?? '圖片'}}</th>
                                        <th rowspan="1" colspan="1">{{trans('default.customer_service_detail_control.created_at') ?? '建立時間'}}</th>
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