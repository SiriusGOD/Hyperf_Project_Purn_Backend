 <div class="card" style="background-color: white;">
     <a href="#privacy-question-accordion-one" class="collapsed" data-bs-toggle="collapse" aria-expanded="false" aria-controls="privacy-collapseOne">
         <div class="card-header" id="privacy-headingOne" style="background-color: darkgray;">
             <h5 class="font-size-14 m-0">
                 {{__('default.search')}}
             </h5>
         </div>
     </a>
     <div id="privacy-question-accordion-one" class="collapse" aria-labelledby="privacy-headingOne" data-bs-parent="#privacy-question-accordion" style="">
         <div class="card-body">
             <form action='{{ route("$main.index") }}'>
                 <div class="col-md-12 text-center search">
                     @foreach($fieldsSetting as $title => $attrs )
                     <div class="cell">
                         @if(isset($attrs['search']) && is_array($attrs['search']) )
                            <label>{{__("$main.titles.$title")}}</label>
                            @if($attrs['type']==='select')
                                @include("backend.components.select" ,[ 'name' =>$title , 'setting' => $attrs , 'main' => $main])
                            @elseif(strpos($attrs['type'],"time" ) > 1 && $attrs['search']['level']=='between' )
                                @include("backend.components.date_between" ,[ 'name' => $title , 'setting' => $attrs , 'main' => $main])
                            @elseif($attrs['type']==='checkbox')
                                <input type="{{$attrs['type']}}" name="{{$title}}"  value="1" />
                            @else
                                <input type="{{$attrs['type']}}" name="{{$title}}"   />
                            @endif
                         @endif
                     </div>
                     @endforeach
                 </div>
                 <br>
                 <div class="col-xs-1 col-sm-1 col-md-1 text-center search">
                     <div class="cell">
                         <button type="submit" class="btn btn-sm btn-primary">{{__('default.search')}}</button>
                     </div>
                 </div>
             </form>

         </div>
     </div>
 </div>

 <link href="/css/search.css" id="bootstrap-style" rel="stylesheet" type="text/css" />