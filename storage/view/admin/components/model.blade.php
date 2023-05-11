 <div class="my-4">

     <!-- Extra Large modal -->
     <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal"
         data-bs-target=".bs-example-modal-xl">{{ __('businesses.titles.new_tags') }}</button>
 </div>
 <!--  Modal content for the above example -->
 <div class="modal fade bs-example-modal-xl" tabindex="-1" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true"
     style="display: none;">
     <div class="modal-dialog modal-xl">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title mt-0" id="myExtraLargeModalLabel"></h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <p class="mb-0">
                     @if (isset($obj))
                         <?php $spData = explode(',', $obj->$name); ?>
                     @else
                         <?php $spData =[]; ?>
                     @endif
                     @foreach (modelsAssociation($setting['association']) as $k => $cell)
                         @if ($k !== 0)
                             <label>
                                 <input type="checkbox" name="tags[]" value="{{ $k }}"
                                     {{ inAryRtStr($k, $spData, 'checked="true"') }}>
                                 {{ $cell }}
                             </label>
                         @endif
                     @endforeach
                 </p>
             </div>
         </div><!-- /.modal-content -->
     </div><!-- /.modal-dialog -->
 </div><!-- /.modal -->
