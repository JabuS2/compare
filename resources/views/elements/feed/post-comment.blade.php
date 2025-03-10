<div class="post-comment d-flex flex-row mb-3" data-commentID="{{$comment->id}}">
    <div class="">
        <img class="rounded-circle" src="{{$comment->author->avatar}}">
    </div>
    <div class="pl-3 w-100">
        <div class="d-flex flex-row justify-content-between">
            <div class="text-bold d-flex align-items-center"><a href="{{route('profile',['username'=>$comment->author->username])}}" class="text-dark-r">{{$comment->author->username}}</a>
                @if($comment->author->email_verified_at && $comment->author->birthdate && ($comment->author->verification && $comment->author->verification->status == 'verified'))
                    <span class="ml-1" data-toggle="tooltip" data-placement="top" title="{{__('Verified user')}}">
                        @include('elements.icon',['icon'=>'checkmark-circle-outline','centered'=>true,'classes'=>'ml-1 text-primary'])
                    </span>
                @endif
            </div>
            <div class="position-absolute separator">
                <div class="d-flex">

                    @if(Auth::user()->id == $comment->author->id || Auth::user()->id == $comment->post->user_id)
                        <span class="ml-1 h-pill h-pill-primary rounded react-button" data-toggle="tooltip" data-placement="top" title="{{__("Delete")}}" onclick="Post.showDeleteCommentDialog({{$comment->post->id}},{{$comment->id}})">
                            @include('elements.icon',['icon'=>'trash-outline'])
                        </span>
                    @else
                        <span class="h-pill h-pill-primary rounded react-button {{PostsHelper::didUserReact($comment->reactions) ? 'active' : ''}}" data-toggle="tooltip" data-placement="top" title="{{__("Like")}}" onclick="Post.reactTo('comment',{{$comment->id}})">
                            @include('elements.icon',['icon'=>'heart-outline'])
                        </span>
                    @endif

                </div>

            </div>
        </div>
        <div>
            <div class="text-break">{{$comment->message}}</div>
            <div class="d-flex text-muted">
                <div>{{$comment->created_at->format('g:i A')}}</div>
                <div class="ml-2">
                    <span class="comment-reactions-label-count">{{count($comment->reactions)}}</span>
                    <span class="comment-reactions-label">{{trans_choice('likes',count($comment->reactions))}}</span>
                </div>
                <div class="ml-2"><a href="javascript:void(0)" onclick="Post.addReplyUser('{{$comment->author->username}}')" class="text-muted">{{__('Reply')}}</a></div>
            </div>
        </div>
    </div>
</div>



<div id="comment-delete-dialog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('Delete Comment') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this comment?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" onclick="Post.deleteComment()">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
