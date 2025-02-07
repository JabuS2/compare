/**
 * Main post class
 */
"use strict";
/* global Swiper, CommentsPaginator, PostsPaginator  */
/* global app */
/* global updateButtonState, redirect, trans, trans_choice, launchToast, mswpScanPage, showDialog, hideDialog, EmojiButton  */


var Post = {

    draftData:{
        text: "",
        attachments:[]
    },

    activePage: 'post',
    postID: null,
    commentID: null,

    /**
     * Sets the current active page
     * @param page
     */
    setActivePage: function(page){
        Post.activePage = page;
    },

    /**
     * Instantiates the media module for post(s)
     * @returns {*}
     */
    initPostsMediaModule: function () {
        return new Swiper(".post-box .mySwiper", {
            // slidesPerColumn:1,
            slidesPerView:'auto',
            pagination: {
                el: ".swiper-pagination",
                // type: "fraction",
                dynamicBullets: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    },

    /**
     * Initiates the gallery swiper module
     * @param gallerySelector
     */
    initGalleryModule: function (gallerySelector = false) {
        mswpScanPage(gallerySelector,'mswp');
    },

    /**
     * Method used for adding a new post comment
     * @param postID
     */
    addComment: function (postID) {
        let postElement = $('*[data-postID="'+postID+'"]');
        let newCommentButton = postElement.find('.new-post-comment-area').find('button');
        updateButtonState('loading',newCommentButton);
        $.ajax({
            type: 'POST',
            data: {
                'message': postElement.find('textarea').val(),
                'post_id': postID
            },
            url: app.baseUrl+'/posts/comments/add',
            success: function (result) {
                if(result.success){
                    launchToast('success',trans('Success'),trans('Comment added'));
                    postElement.find('.no-comments-label').addClass('d-none');
                    postElement.find('.post-comments-wrapper').prepend(result.data).fadeIn('slow');
                    postElement.find('textarea').val('');
                    const commentsCount = parseInt(postElement.find('.post-comments-label-count').html()) + 1;
                    postElement.find('.post-comments-label-count').html(commentsCount);
                    postElement.find('.post-comments-label').html(trans_choice('comments',commentsCount));
                    updateButtonState('loaded',newCommentButton);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                    updateButtonState('loaded',newCommentButton);
                }
                newCommentButton.blur();
            },
            error: function (result) {
                postElement.find('textarea').addClass('is-invalid');
                if(result.status === 422) {
                    $.each(result.responseJSON.errors,function (field,error) {
                        if(field === 'message'){
                            postElement.find('textarea').parent().find('.invalid-feedback').html(error);
                        }
                    });
                    updateButtonState('loaded',newCommentButton);
                }
                else if(result.status === 403 || result.status === 404){
                    launchToast('danger',trans('Error'), result.responseJSON.message);
                }
                newCommentButton.blur();
            }
        });
    },

    /**
     * Shows up post comment delete dialog confirmation dialog
     * @param postID
     * @param commentID
     */
    showDeleteCommentDialog: function(postID, commentID){
        showDialog('comment-delete-dialog');
        Post.commentID = commentID;
        Post.postID = postID;
    },

    /**
     * Deletes post comment
     */
    deleteComment: function(){
        let commentElement = $('*[data-commentID="'+Post.commentID+'"]');
        let postElement = $('*[data-postID="'+Post.postID+'"]');
        $.ajax({
            type: 'DELETE',
            data: {
                'id': Post.commentID
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/comments/delete',
            success: function (result) {
                if(result.success){
                    commentElement.fadeOut("normal", function() {
                        $(this).remove();
                        if(postElement.find('.post-comment').length === 0){
                            postElement.find('.no-comments-label').removeClass('d-none');
                        }

                    });

                    const commentsCount = parseInt(postElement.find('.post-comments-label-count').html()) - 1;
                    postElement.find('.post-comments-label-count').html(commentsCount);
                    postElement.find('.post-comments-label').html(trans_choice('comments',commentsCount));

                    launchToast('success',trans('Success'),result.message);
                    hideDialog('comment-delete-dialog');
                }
                else{

                    launchToast('danger',trans('Error'),result.errors[0]);
                    $('#comment-delete-dialog').modal('hide');
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
                hideDialog('comment-delete-dialog');
            }
        });

    },

    /**
     * Toggle post comment area visibility
     * @param post_id
     */
    showPostComments: function(post_id){
        let postElement = $('*[data-postID="'+post_id+'"] .post-comments');

        // No pagination needed - on feed
        if(typeof postVars === 'undefined'){
            CommentsPaginator.nextPageUrl = '';
        }

        if(CommentsPaginator.nextPageUrl === ''){
            CommentsPaginator.init(app.baseUrl+'/posts/comments',postElement.find('.post-comments-wrapper'));
        }

        const isHidden = postElement.hasClass('d-none');
        if(isHidden){
            if(!postElement.hasClass('latest-comments-loaded')){
                CommentsPaginator.loadResults(post_id,9);
            }
            postElement.removeClass('d-none');
            postElement.addClass('latest-comments-loaded');
        }
        else{
            postElement.addClass('d-none');
        }

        Post.initEmojiPicker(post_id);

    },

    /**
     * Instantiates the emoji picker for any given post
     * @param post_id
     */
    initEmojiPicker: function(post_id){
        try{
            const button = document.querySelector('*[data-postID="'+post_id+'"] .trigger');
            const picker = new EmojiButton(
                {
                    position: 'top-end',
                    theme: app.theme,
                    autoHide: false,
                    rows: 4,
                    recentsCount: 16,
                    emojiSize: '1.3em',
                    showSearch: false,
                }
            );
            picker.on('emoji', emoji => {
                document.querySelector('input').value += emoji;
                $('*[data-postID="'+post_id+'"] .comment-textarea').val($('*[data-postID="'+post_id+'"] .comment-textarea').val() + emoji);

            });
            button.addEventListener('click', () => {
                picker.togglePicker(button);
            });
        }
        catch (e) {
            // Maybe avoid ending up in here entirely
            // console.error(e)
        }

    },

    /**
     * Add new reaction
     * Can be used for post or comment reactionn
     * @param type
     * @param id
     */
    reactTo: function (type,id) {
        let reactElement = null;
        let reactionsCountLabel = null;
        let reactionsLabel = null;
        if(type === 'post'){
            reactElement = $('*[data-postID="'+id+'"] .post-footer .react-button');
            reactionsCountLabel = $('*[data-postID="'+id+'"] .post-footer .post-reactions-label-count');
            reactionsLabel = $('*[data-postID="'+id+'"] .post-footer .post-reactions-label');
        }
        else{
            reactElement = $('*[data-commentID="'+id+'"] .react-button');
            reactionsCountLabel = $('*[data-commentID="'+id+'"] .comment-reactions-label-count');
            reactionsLabel = $('*[data-commentID="'+id+'"] .comment-reactions-label');
        }
        const didReact = reactElement.hasClass('active');
        if (didReact) {
            reactElement.removeClass('active');
            reactElement.find('svg').replaceWith(`
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-medium" viewBox="0 0 512 512">
                    <path d="M352.92 80C288 80 256 144 256 144s-32-64-96.92-64c-52.76 0-94.54 44.14-95.08 96.81-1.1 109.33 86.73 187.08 183 252.42a16 16 0 0018 0c96.26-65.34 184.09-143.09 183-252.42-.54-52.67-42.32-96.81-95.08-96.81z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/>
                </svg>
            `);
        } else {
            reactElement.addClass('active');
            reactElement.find('svg').replaceWith(`
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-medium text-primary" viewBox="0 0 512 512">
                    <path d="M256 448a32 32 0 01-18-5.57c-78.59-53.35-112.62-89.93-131.39-112.8-40-48.75-59.15-98.8-58.61-153C48.63 114.52 98.46 64 159.08 64c44.08 0 74.61 24.83 92.39 45.51a6 6 0 009.06 0C278.31 88.81 308.84 64 352.92 64c60.62 0 110.45 50.52 111.08 112.64.54 54.21-18.63 104.26-58.61 153-18.77 22.87-52.8 59.45-131.39 112.8a32 32 0 01-18 5.56z"/>
                </svg>
            `);
        }
        
        $.ajax({
            type: 'POST',
            data: {
                'type': type,
                'action': (didReact === true ? 'remove' : 'add'),
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/reaction',
            success: function (result) {
                if(result.success){
                    let count = parseInt(reactionsCountLabel.html());
                    if(didReact){
                        count--;
                    }
                    else{
                        count++;
                    }
                    reactionsCountLabel.html(count);
                    reactionsLabel.html(trans_choice('likes',count));
                    // launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Appends replied username to comment field
     * @param username
     */
    addReplyUser: function(username){
        $('.new-post-comment-area textarea').val($('.new-post-comment-area textarea').val()+ ' @' +username+ ' ');
    },

    /**
     * Shows up the post removal confirmation box
     * @param post_id
     */
    confirmPostRemoval: function (post_id) {
        Post.postID = post_id;
        $('#post-delete-dialog').modal('show');
    },

    /**
     * Removes user post
     */
    removePost: function(){
        let postElement = $('*[data-postID="'+Post.postID+'"]');
        $.ajax({
            type: 'DELETE',
            data: {
                'id': Post.postID
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/delete',
            success: function (result) {
                if(result.success){
                    if(Post.activePage !== 'post'){
                        $('#post-delete-dialog').modal('hide');
                        postElement.fadeOut("normal", function() {
                            $(this).remove();
                        });
                    }
                    else{
                        if(document.referrer.indexOf('feed') > 0){
                            redirect(app.baseUrl + '/feed');
                        }
                        else{
                            redirect(document.referrer);
                        }
                    }
                    launchToast('success',trans('Success'),result.message);

                }
                else{
                    $('#post-delete-dialog').modal('hide');
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Adds or removes user bookmarks
     * @param id
     */
    togglePostBookmark: function (id) {
        let reactElement = $('*[data-postID="'+id+'"] .bookmark-button');
        const isBookmarked = reactElement.hasClass('is-active');
        $.ajax({
            type: 'POST',
            data: {
                'action': (isBookmarked === true ? 'remove' : 'add'),
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/bookmark',
            success: function (result) {
                if(result.success){
                    if(isBookmarked){
                        reactElement.removeClass('is-active');
                        reactElement.html(trans('Bookmark this post'));
                    }
                    else{
                        reactElement.addClass('is-active');
                        reactElement.html(trans('Remove this bookmark'));
                    }

                    launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Function used to pin/unpin a post
     * @param id
     */
    togglePostPin: function (id) {
        let reactElement = $('*[data-postID="'+id+'"] .pin-button');
        const isPinned = reactElement.hasClass('is-active');
        $('.pinned-post-label').addClass('d-none')
        $.ajax({
            type: 'POST',
            data: {
                'action': (isPinned === true ? 'remove' : 'add'),
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/pin',
            success: function (result) {
                if(result.success){
                    if(isPinned){
                        $('*[data-postID="'+id+'"] .pinned-post-label').addClass('d-none')
                        reactElement.removeClass('is-active');
                        reactElement.html(trans('Pin this post'));
                    }
                    else{
                        $('*[data-postID="'+id+'"] .pinned-post-label').removeClass('d-none')
                        reactElement.addClass('is-active');
                        reactElement.html(trans('Un-pin post'));
                    }

                    launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Disabling right for posts ( if site wise setting is set to do it )
     */
    disablePostsRightClick: function () {
        $(".post-media, .pswp__item").unbind('contextmenu');
        $(".post-media, .pswp__item").on("contextmenu",function(){
            return false;
        });
    },

    /**
     * Toggles profile's description
     */
    toggleFullDescription:function (postID) {
        let postElement = $('*[data-postID="'+postID+'"]');
        $('*[data-postID="'+postID+'"] .label-less, *[data-postID="'+postID+'"] .label-more').addClass('d-none');
        if(postElement.find('.post-content-data').hasClass('line-clamp-1')){
            postElement.find('.post-content-data').removeClass('line-clamp-1');
            postElement.find('.label-less').removeClass('d-none');
        }
        else{
            postElement.find('.post-content-data').addClass('line-clamp-1');
            postElement.find('.label-more').removeClass('d-none');
        }
        PostsPaginator.scrollToLastPost(postID);
    },

};

