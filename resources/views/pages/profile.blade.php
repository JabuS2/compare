@extends('layouts.user-no-nav')

@section('page_title',  __("user_profile_title_label",['user' => $user->name]))
@section('share_url', route('home'))
@section('share_title',  __("user_profile_title_label",['user' => $user->name]) . ' - ' .  getSetting('site.name'))
@section('share_description', $seo_description ?? getSetting('site.description'))
@section('share_type', 'article')
@section('share_img', $user->cover)

@section('scripts')
    {!!
        Minify::javascript(array_merge([
            '/js/PostsPaginator.js',
            '/js/CommentsPaginator.js',
            '/js/StreamsPaginator.js',
            '/js/Post.js',
            '/js/pages/profile.js',
            '/js/pages/lists.js',
            '/js/pages/checkout.js',
            '/libs/swiper/swiper-bundle.min.js',
            '/js/plugins/media/photoswipe.js',
            '/libs/photoswipe/dist/photoswipe-ui-default.min.js',
            '/libs/@joeattardi/emoji-button/dist/index.js',
            '/js/plugins/media/mediaswipe.js',
            '/js/plugins/media/mediaswipe-loader.js',
            '/js/LoginModal.js',
            '/js/messenger/messenger.js',
         ],$additionalAssets))->withFullUrl()
    !!}

<style>
.instagram-link-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 50px; /* Ou outro tamanho conforme necessário */
    height: 50px; /* Deve ser o mesmo que a largura para criar um círculo */
    border-radius: 50%; /* Isso cria o efeito de círculo */
    margin: auto auto auto 25px;
    /* Efeito de pixel simulado usando box-shadow */
}

.instagram-icon {
    /* Ajuste essas propriedades conforme necessário para alinhar o ícone */
    color: #fff; /* Cor do ícone */
    font-size: 24px; /* Tamanho do ícone, ajuste conforme necessário */
    width: 40px; /* Ou outro tamanho conforme necessário */
    height: 40px; /* Deve ser o mesmo que a largura para criar um círculo */

}

/* Container com padding ajustado */
.container {
    padding-top: 2rem !important;
    margin: 0 auto; /* Centraliza o conteúdo */
    max-width: 100%; /* Garante que ocupe toda a largura disponível */
}

/* Estilos para a grade */
.grid-view {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 colunas */
    grid-gap: 5px; /* Espaçamento de 5px entre os itens */
    width: 100%; /* Garante que ocupe 100% da largura do contêiner */
    margin: 0 auto; /* Centraliza a grade dentro do contêiner */
}

/* Ajuste dos cards */
.grid-view .post-box {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1; /* Define uma proporção de 1:1 (quadrado) */
    overflow: hidden;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.grid-view .post-box:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Conteúdo da mídia */
.grid-view .post-box .post-media {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 0; /* Removido o margin negativo */
    margin-right: 0;
}

.grid-view .post-media {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Ajuste para cobrir corretamente o contêiner */
.grid-view .post-media img,
.grid-view .post-media video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center; /* Centraliza a mídia dentro do contêiner */
    display: block; /* Remove espaçamento inline */
    margin: 0;
    padding: 0;
}

.grid-view .post-box:hover .post-media img,
.grid-view .post-box:hover .post-media video {
    transform: scale(1.05);
}

/* Esconder o header e o conteúdo do post */
.grid-view .post-box .post-header,
.grid-view .post-box .post-content,
.grid-view .post-box .post-footer,
.grid-view .post-box .footer-actions {
    display: none;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>



    // Verifica se os dados de pagamento da Suitpay estão definidos na sessão e exibe o modal do código QR da Suitpay
    @if (Session::has('suitpay_payment_data') && Session::get('suitpay_payment_data')['user_id'] == Auth::user()->id)
        $(document).ready(function() {
            // Função para fechar o modal e recarregar a página
            function closeAndReload() {
                $('#suitpayQrcodeModal').modal('hide');
                location.reload(true); // Use 'true' para recarregar a página do servidor
            }

            // Verifica se o modal já foi exibido anteriormente
            if (localStorage.getItem('suitpayModalDisplayed') !== 'true') {
                $('#suitpayQrcodeModal').modal('show');

                // Adiciona um ouvinte de eventos para recarregar a página quando o modal for fechado manualmente
                $('#suitpayQrcodeModal').on('hidden.bs.modal', function (e) {
                    // Destrói a sessão e fecha o modal manualmente
                    $.ajax({
                        url: '/suitpay/destroy-session',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function(response) {
                            console.log(response);
                            closeAndReload();
                        },
                        error: function() {
                            closeAndReload();
                        }
                    });
                });

                // Oculta o modal após 30 segundos e destrói a sessão
                setTimeout(function() {
                    $.ajax({
                        url: '/suitpay/destroy-session',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function(response) {
                            console.log(response);
                            // Verifica se o pagamento foi bem-sucedido
                            if (response.status === 'PAID_OUT') {
                                // Define o indicador de que o modal foi exibido para evitar mostrá-lo novamente
                                localStorage.setItem('suitpayModalDisplayed', 'true');
                            }
                            // Fecha o modal após a verificação do pagamento, mesmo que não seja bem-sucedido
                            closeAndReload();
                        },
                        error: function() {
                            // Em caso de erro na solicitação AJAX, também fecha o modal e recarrega a página
                            closeAndReload();
                        }
                    });
                }, 30000);
            }
        });
    @endif

// Estado global de visualização
var currentView = 'list'; // Valor inicial é 'list', então ele inicia sem a classe 'h-100'

// Função para alternar a visualização entre 'grid' e 'list'
function changeView(view) {
    currentView = view; // Atualiza o estado global
    applyViewToNewElements();

    var container = document.getElementById('posts-container');
    var removeLines = document.querySelectorAll('.removelinha'); // Seleciona todos os <hr> com a classe "removelinha"
    var removeswiperbutton = document.querySelectorAll('.swiper-button');
    var h100Elements = document.querySelectorAll('.h-100-target'); // Atualizado: Aplica 'h-100' apenas a elementos com a classe 'h-100-target'

    if (view === 'grid') {
        container.classList.add('grid-view');
        container.classList.remove('list-view');

        // Adiciona 'd-none' a todos os <hr>
        removeLines.forEach(function(line) {
            line.classList.add('d-none');
        });

        removeswiperbutton.forEach(function(line) {
            line.classList.add('d-none');
        });

        // Garante que a classe 'h-100' seja aplicada no modo grid
        h100Elements.forEach(function(element) {
            element.classList.add('h-100');
        });

    } else {
        container.classList.add('list-view');
        container.classList.remove('grid-view');

        // Remove 'd-none' de todos os <hr>
        removeLines.forEach(function(line) {
            line.classList.remove('d-none');
        });

        removeswiperbutton.forEach(function(line) {
            line.classList.remove('d-none');
        });

        // Remove a classe 'h-100' no modo list
        h100Elements.forEach(function(element) {
            element.classList.remove('h-100');
        });
    }
}

// Função para aplicar a visualização inicial e para novos elementos com a classe "h-100-target"
function applyViewToNewElements() {
    var removeLines = document.querySelectorAll('.removelinha');
    var removeswiperbutton = document.querySelectorAll('.swiper-button');
    var h100Elements = document.querySelectorAll('.h-100-target');

    if (currentView === 'grid') {
        removeLines.forEach(function(line) {
            line.classList.add('d-none');
        });

        removeswiperbutton.forEach(function(line) {
            line.classList.add('d-none');
        });

        h100Elements.forEach(function(element) {
            element.classList.add('h-100');
        });

    } else {
        removeLines.forEach(function(line) {
            line.classList.remove('d-none');
        });

        removeswiperbutton.forEach(function(line) {
            line.classList.remove('d-none');
        });

        h100Elements.forEach(function(element) {
            element.classList.remove('h-100');
        });
    }
}

// Função para inicializar o estado correto ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    applyViewToNewElements(); // Aplica a visualização inicial em modo 'list'
    observeRemovelinhaClass(); // Observa periodicamente a classe "removelinha"
});

// Função que verifica periodicamente a existência de elementos com a classe "removelinha"
function observeRemovelinhaClass() {
    setInterval(function() {
        applyViewToNewElements();
    }, 1000); // Intervalo de 1 segundo (1000 ms)
}


</script>




@stop

@section('styles')
    {!!
        Minify::stylesheet([
            '/css/pages/profile.css',
            '/css/pages/checkout.css',
            '/css/pages/lists.css',
            '/libs/swiper/swiper-bundle.min.css',
            '/libs/photoswipe/dist/photoswipe.css',
            '/libs/photoswipe/dist/default-skin/default-skin.css',
            '/css/pages/profile.css',
            '/css/pages/lists.css',
            '/css/posts/post.css'
         ])->withFullUrl()
    !!}
    @if(getSetting('feed.post_box_max_height'))
        @include('elements.feed.fixed-height-feed-posts', ['height' => getSetting('feed.post_box_max_height')])
    @endif
@stop

@section('meta')
    @if(getSetting('security.recaptcha_enabled') && !Auth::check())
        {!! NoCaptcha::renderJs() !!}
    @endif
    @if($activeFilter)
        <link rel="canonical" href="{{route('profile',['username'=> $user->username])}}" />
    @endif
@stop

@section('content')
    <div class="row">
        <div class="min-vh-100 col-12 col-md-8 border-right pr-md-0">

            <div class="">
                <div class="profile-cover-bg">
                    <img class="card-img-top centered-and-cropped" src="{{$user->cover}}">
                </div>
            </div>

            <div class="container d-flex justify-content-between align-items-center">
                <div class="z-index-3 avatar-holder">
                    <img src="{{$user->avatar}}" class="rounded-circle">
                </div>
                <div>
                    @if(!Auth::check() || Auth::user()->id !== $user->id)
                        <div class="d-flex flex-row">
                            @if(Auth::check())
                                <div class="">
                                <span class="p-pill ml-2 pointer-cursor to-tooltip"
                                      @if(!Auth::user()->email_verified_at && getSetting('site.enforce_email_validation'))
                                      data-placement="top"
                                      title="{{__('Please verify your account')}}"
                                      @elseif(!\App\Providers\GenericHelperServiceProvider::creatorCanEarnMoney($user))
                                      data-placement="top"
                                      title="{{__('This creator cannot earn money yet')}}"
                                      @else
                                      data-placement="top"
                                      title="{{__('Send a tip')}}"
                                      data-toggle="modal"
                                      data-target="#checkout-center"
                                      data-type="tip"
                                      data-first-name="{{Auth::user()->first_name}}"
                                      data-last-name="{{Auth::user()->last_name}}"
                                      data-billing-address="{{Auth::user()->billing_address}}"
                                      data-country="{{Auth::user()->country}}"
                                      data-city="{{Auth::user()->city}}"
                                      data-state="{{Auth::user()->state}}"
                                      data-postcode="{{Auth::user()->postcode}}"
                                      data-available-credit="{{Auth::user()->wallet->total}}"
                                      data-username="{{$user->username}}"
                                      data-name="{{$user->name}}"
                                      data-avatar="{{$user->avatar}}"
                                      data-recipient-id="{{$user->id}}"
                                      @endif
                                >
                                 @include('elements.icon',['icon'=>'cash-outline'])
                                </span>
                                </div>
                                <div class="">
                                    @if($hasSub || $viewerHasChatAccess)
                                        <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Send a message')}}" onclick="messenger.showNewMessageDialog()">
                                            @include('elements.icon',['icon'=>'chatbubbles-outline'])
                                        </span>
                                    @else
                                        <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('DMs unavailable without subscription')}}">
                                        @include('elements.icon',['icon'=>'chatbubbles-outline'])
                                    </span>
                                    @endif
                                </div>
                                <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Add to your lists')}}" onclick="Lists.showListAddModal();">
                                 @include('elements.icon',['icon'=>'list-outline'])
                            </span>
                            @endif
                            @if(getSetting('profiles.allow_profile_qr_code'))
                                <div>
                                    <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Get profile QR code')}}" onclick="Profile.getProfileQRCode()">
                                        @include('elements.icon',['icon'=>'qr-code-outline'])
                                    </span>
                                </div>
                            @endif
                            <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Copy profile link')}}" onclick="toggleDrawer()">
                                 @include('elements.icon',['icon'=>'share-social-outline'])
                            </span>
                        </div>
                    @else
                        <div class="d-flex flex-row">
                            <div class="mr-2">
                                <a href="{{route('my.settings')}}" class="p-pill p-pill-text ml-2 pointer-cursor">
                                    @include('elements.icon',['icon'=>'settings-outline','classes'=>'mr-1'])
                                    <span class="d-none d-md-block">{{__('Edit profile')}}</span>
                                    <span class="d-block d-md-none">{{__('Edit')}}</span>
                                </a>
                            </div>
                            @if(getSetting('profiles.allow_profile_qr_code'))
                                <div>
                                    <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Get profile QR code')}}" onclick="Profile.getProfileQRCode()">
                                        @include('elements.icon',['icon'=>'qr-code-outline'])
                                    </span>
                                </div>
                            @endif
                            <div>
                                <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Copy profile link')}}" onclick="shareOrCopyLink()">
                                    @include('elements.icon',['icon'=>'share-social-outline'])
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="container pt-2 pl-0 pr-0">

                <div class="pt-2 pl-4 pr-4">
                    <h5 class="text-bold d-flex align-items-center">
                        <span>{{$user->name}}</span>
                        @if($user->email_verified_at && $user->birthdate && ($user->verification && $user->verification->status == 'verified'))
                            <span data-toggle="tooltip" data-placement="top" title="{{__('Verified user')}}">
                                @include('elements.icon',['icon'=>'checkmark-circle-outline','centered'=>true,'classes'=>'ml-1 text-primary'])
                            </span>
                        @endif
                        @if($hasActiveStream)
                            <span data-toggle="tooltip" data-placement="right" title="{{__('Live streaming')}}">
                            <div class="blob red ml-3"></div>
                            </span>
                        @endif
                    </h5>
                    <h6 class="text-muted"><span class="text-bold"><span>@</span>{{$user->username}}</span> {{--- Last seen X time ago--}}</h6>
                </div>

                <div class="pt-2 pb-2 pl-4 pr-4 profile-description-holder">
                    <div class="description-content {{$user->bio && (strlen(trim(strip_tags(GenericHelper::parseProfileMarkdownBio($user->bio)))) >= 85 || substr_count($user->bio,"\r\n") > 1) &&  !getSetting('profiles.disable_profile_bio_excerpt') ? 'line-clamp-1' : ''}}">
                        @if($user->bio)
                            @if(getSetting('profiles.allow_profile_bio_markdown'))
                                {!!  GenericHelper::parseProfileMarkdownBio($user->bio) !!}
                            @else
                                {{$user->bio}}
                            @endif
                        @else
                            {{__('No description available.')}}
                        @endif
                    </div>
                    @if($user->bio && (strlen(trim(strip_tags(GenericHelper::parseProfileMarkdownBio($user->bio)))) >= 85 || substr_count($user->bio,"\r\n") > 1) && !getSetting('profiles.disable_profile_bio_excerpt'))
                        <span class="text-primary pointer-cursor" onclick="Profile.toggleFullDescription()">
                            <span class="label-more">{{__('More info')}}</span>
                            <span class="label-less d-none">{{__('Show less')}}</span>
                        </span>
                    @endif
                </div>
                @if(!getSetting('profiles.disable_website_link_on_profile'))
                    @if($user->website)
                        <div class="instagram-link-container">
                            <a href="{{ $user->website }}" target="_blank" rel="nofollow">
                                <img class="instagram-icon" src="{{ asset('img/logos/instagram.png') }}" alt="Instagram">
                            </a>
                        </div>
                    @endif
                @endif
                <div class="d-flex flex-column flex-md-row justify-content-md-between pb-2 pl-4 pr-4 mb-3 mt-1">

                    <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
                        @include('elements.icon',['icon'=>'calendar-clear-outline','centered'=>false,'classes'=>'mr-1'])
                        <div class="text-truncate ml-1">
                            {{ucfirst($user->created_at->translatedFormat('F d'))}}
                        </div>
                    </div>
                    @if($user->location)
                        <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
                            @include('elements.icon',['icon'=>'location-outline','centered'=>false,'classes'=>'mr-1'])
                            <div class="text-truncate ml-1">
                                {{$user->location}}
                            </div>
                        </div>
                    @endif

                    @if(getSetting('profiles.allow_gender_pronouns'))
                        @if($user->gender_pronoun)
                            <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
                                @include('elements.icon',['icon'=>'male-female-outline','centered'=>false,'classes'=>'mr-1'])
                                <div class="text-truncate ml-1">
                                    {{$user->gender_pronoun}}
                                </div>
                            </div>
                        @endif
                    @endif

                </div>

                <div class="bg-separator border-top border-bottom"></div>

                @include('elements.message-alert',['classes'=>'px-2 pt-4'])
                @if($user->paid_profile && (!getSetting('profiles.allow_users_enabling_open_profiles') || (getSetting('profiles.allow_users_enabling_open_profiles') && !$user->open_profile)))
                    @if( (!Auth::check() || Auth::user()->id !== $user->id) && !$hasSub)
                        <div class="p-4 subscription-holder">
                            <h6 class="font-weight-bold text-uppercase mb-3">{{__('Subscription')}}</h6>
                            @if(count($offer))
                                <h5 class="m-0 text-bold">{{__('Limited offer main label',['discount'=> round($offer['discountAmount']), 'days_remaining'=> $offer['daysRemaining'] ])}}</h5>
                                <small class="">{{__('Offer ends label',['date'=>$offer['expiresAt']->format('d M')])}}</small>
                            @endif
                            @if($hasSub)
                                <button onclick="send_initial_checkout_pixels()" class="btn btn-round btn-lg btn-primary btn-block mt-3 mb-2 text-center">
                                    <span>{{__('Subscribed')}}</span>
                                </button>
                            @else

                                @if(Auth::check())
                                    @if(!GenericHelper::isEmailEnforcedAndValidated())
                                        <i>{{__('Your email address is not verified.')}} <a href="{{route('verification.notice')}}">{{__("Click here")}}</a> {{__("to re-send the confirmation email.")}}</i>
                                    @endif
                                @endif

                                @include('elements.checkout.subscribe-button-30')
                                <div class="d-flex justify-content-between">
                                    @if($user->profile_access_price_6_months || $user->profile_access_price_12_months)
                                        <small>
                                            <div class="pointer-cursor d-flex align-items-center" onclick="Profile.toggleBundles()">
                                                <div class="label-more">{{__('Subscriptions bundles')}}</div>
                                                <div class="label-less d-none">{{__('Hide bundles')}}</div>
                                                <div class="ml-1 label-icon">
                                                    @include('elements.icon',['icon'=>'chevron-down-outline','centered'=>false])
                                                </div>
                                            </div>
                                        </small>
                                    @endif
                                    @if(count($offer))
                                        <small class="">{{__('Regular price label',['currency'=> getSetting('payments.currency_code') ?? 'USD','amount'=>$user->offer->old_profile_access_price])}}</small>
                                    @endif
                                </div>

                                @if($user->profile_access_price_6_months || $user->profile_access_price_12_months || $user->profile_access_price_3_months)
                                    <div class="subscription-bundles d-none mt-4">
                                        @if($user->profile_access_price_3_months)
                                            @include('elements.checkout.subscribe-button-90')
                                        @endif

                                        @if($user->profile_access_price_6_months)
                                            @include('elements.checkout.subscribe-button-182')
                                        @endif

                                        @if($user->profile_access_price_12_months)
                                            @include('elements.checkout.subscribe-button-365')
                                        @endif

                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="bg-separator border-top border-bottom"></div>
                    @endif
                @elseif(!Auth::check() || (Auth::check() && Auth::user()->id !== $user->id))
                    <div class=" p-4 subscription-holder">
                        <h6 class="font-weight-bold text-uppercase mb-3">{{__('Follow this creator')}}</h6>
                        @if(Auth::check())
                            <button class="btn btn-round btn-lg btn-primary btn-block mt-3 mb-0 manage-follow-button" onclick="Lists.manageFollowsAction('{{$user->id}}')">
                                <span class="manage-follows-text">{{\App\Providers\ListsHelperServiceProvider::getUserFollowingType($user->id, true)}}</span>
                            </button>
                        @else
                            <button onclick="send_initial_checkout_pixels()" class="btn btn-round btn-lg btn-primary btn-block mt-3 mb-0 text-center"
                                    data-toggle="modal"
                                    data-target="#login-dialog"
                            >
                                <span class="">{{__('Follow')}}</span>
                            </button>
                        @endif
                    </div>
                    <div class="bg-separator border-top border-bottom"></div>
                @endif



                <div class="mt-3 inline-border-tabs">

                    <!-- Novo dropdown para tipo de postagem (paga ou gratuita) -->
                    <div class="dropdown d-inline-block ms-2" style="padding-left: 5px; !important">
                        <button class="btn dropdown-toggle" type="button" id="paidFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid red; border-radius: 10px; background: transparent; color: red; font-weight: bold; font-size: 14px;">
                            {{ $paidFilter == 'all' ? 'Pagos / Gratuitos' : ($paidFilter == 'paid' ? 'Pagos' : 'Gratuitos') }}
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="paidFilterDropdown">
                            <!-- Opção Todos -->
                            <li>
                                <a class="dropdown-item {{ $paidFilter == 'all' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?paidFilter=all' }}" style="{{ $paidFilter == 'all' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                    Pagos/Gratuitos
                                </a>
                            </li>
                            <!-- Opção Pagos -->
                            <li>
                                <a class="dropdown-item {{ $paidFilter == 'paid' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?paidFilter=paid' }}" style="{{ $paidFilter == 'paid' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                    Pagos
                                </a>
                            </li>
                            <!-- Opção Gratuitos -->
                            <li>
                                <a class="dropdown-item {{ $paidFilter == 'free' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?paidFilter=free' }}" style="{{ $paidFilter == 'free' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                    Gratuitos
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Dropdown para filtro -->
                    <div class="dropdown d-inline-block">
                        <button class="btn dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid red; border-radius: 10px; background: transparent; color: red; font-weight: bold; font-size: 14px;">
                            {{ $activeFilter == false ? 'Todos' : ucfirst(trans_choice($activeFilter, 2)) }} ({{ $filterTypeCounts[$activeFilter] ?? $posts->total() }})
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                            <!-- Todos -->
                            <li>
                                <a class="dropdown-item {{ $activeFilter == false ? 'active' : '' }}" href="{{ route('profile', ['username'=> $user->username]) }}" style="{{ $activeFilter == false ? 'background-color: red; color: white;' : 'color: red;' }}">
                                    Todos ({{ $posts->total() }})
                                </a>
                            </li>

                            <!-- Imagens -->
                            @if($filterTypeCounts['image'] > 0)
                                <li>
                                    <a class="dropdown-item {{ $activeFilter == 'image' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?filter=image' }}" style="{{ $activeFilter == 'image' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                        Imagens ({{ $filterTypeCounts['image'] }})
                                    </a>
                                </li>
                            @endif

                            <!-- Vídeos -->
                            @if($filterTypeCounts['video'] > 0)
                                <li>
                                    <a class="dropdown-item {{ $activeFilter == 'video' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?filter=video' }}" style="{{ $activeFilter == 'video' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                        Vídeos ({{ $filterTypeCounts['video'] }})
                                    </a>
                                </li>
                            @endif

                            <!-- Áudio -->
                            @if($filterTypeCounts['audio'] > 0)
                                <li>
                                    <a class="dropdown-item {{ $activeFilter == 'audio' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?filter=audio' }}" style="{{ $activeFilter == 'audio' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                        Áudio ({{ $filterTypeCounts['audio'] }})
                                    </a>
                                </li>
                            @endif

                            <!-- Streams -->
                            @if(getSetting('streams.allow_streams') && isset($filterTypeCounts['streams']) && $filterTypeCounts['streams'] > 0)
                                <li>
                                    <a class="dropdown-item {{ $activeFilter == 'streams' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) . '?filter=streams' }}" style="{{ $activeFilter == 'streams' ? 'background-color: red; color: white;' : 'color: red;' }}">
                                        Streams ({{ $filterTypeCounts['streams'] }})
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Ícones de Grid e List -->
                    <div class="d-inline-block ms-3">
                        <button onclick="changeView('grid')" class="btn btn-transparent p-0" title="Grid View">
                            <img src="{{ asset('img/IconeGrid.png') }}" alt="Grid View" width="24" height="24">
                        </button>
                        <button onclick="changeView('list')" class="btn btn-transparent p-0 ms-2" title="List View">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#be1523" viewBox="0,0,256,256">
                                <g transform="scale(10.66667,10.66667)">
                                    <path d="M20,2h-16c-1.10457,0 -2,0.89543 -2,2v8c0,1.10457 0.89543,2 2,2h16c1.10457,0 2,-0.89543 2,-2v-8c0,-1.10457 -0.89543,-2 -2,-2zM4,12v-8h16v8zM22,16v2h-20v-2zM22,20v2h-20v-2z"></path>
                                </g>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Restante do layout -->
                <div class="justify-content-center align-items-center {{(Cookie::get('app_feed_prev_page') && PostsHelper::isComingFromPostPage(request()->session()->get('_previous'))) ? 'mt-3' : 'mt-4'}}">
                    @if($activeFilter !== 'streams')
                        @include('elements.feed.posts-load-more', ['classes' => 'mb-2'])
                        <div class="feed-box mt-0 posts-wrapper" id="posts-container">
                            @include('elements.feed.posts-wrapper', ['posts' => $posts])
                        </div>
                    @else
                        <div class="streams-box mt-4 streams-wrapper mb-4">
                            @include('elements.search.streams-wrapper', ['streams' => $streams, 'showLiveIndicators' => true, 'showUsername' => false])
                        </div>
                    @endif
                    @include('elements.feed.posts-loading-spinner')
                </div>






            </div>
        </div>
        <div class="col-12 col-md-4 d-none d-md-block pt-3">
            @include('elements.profile.widgets')
        </div>
    </div>

    <div class="d-none">
        <ion-icon name="heart"></ion-icon>
        <ion-icon name="heart-outline"></ion-icon>
    </div>

    @if(Auth::check())
        @include('elements.lists.list-add-user-dialog',['user_id' => $user->id, 'lists' => ListsHelper::getUserLists()])
        @include('elements.checkout.checkout-box')
        @include('elements.messenger.send-user-message',['receiver'=>$user])
    @else
        @include('elements.modal-login')
    @endif

    @include('elements.profile.qr-code-dialog')

@stop

@php
    $appTheme = isset($_COOKIE['app_theme']) && $_COOKIE['app_theme'] === 'dark' ? 'dark' : 'light';
@endphp


@php
    // Verifica o tema do cookie e define a variável $appTheme
    $appTheme = isset($_COOKIE['app_theme']) && $_COOKIE['app_theme'] === 'dark' ? 'dark' : 'light';
@endphp

<div id="profile-drawer" class="drawer d-none bg-{{ $appTheme }}">
    <div class="drawer-header d-flex justify-content-between align-items-center mb-3">
        <p class="h5 text-{{ $appTheme === 'dark' ? 'light' : 'dark' }}">Divulgue seu perfil</p>
        <svg class="icon-close cursor-pointer" viewBox="0 0 24 24" onclick="toggleDrawer()">
            <path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
        </svg>
    </div>
    <div class="drawer-content">
        <div class="mb-4">
            <p class="font-weight-bold text-{{ $appTheme === 'dark' ? 'white' : 'dark' }}">Link para o perfil</p>
            <p class="text-{{ $appTheme === 'dark' ? 'muted' : 'secondary' }}">Divulgação do seu perfil no Closyflix.</p>
            <div class="link-row d-flex flex-wrap mb-2">
                <div class="profile-link-container">
                    <span class="profile-link">closyflix.com/{{ $user->username }}</span>
                    <button class="btn btn-primary btn-sm ml-auto" style="margin-bottom: 0px !important; margin-left: 6px !important" onclick="copyToClipboard('closyflix.com/{{ $user->username }}')">Copiar</button>
                </div>
            </div>
        </div>

        <hr class="bg-{{ $appTheme === 'dark' ? 'secondary' : 'light' }}">

        <!-- <div>
            <p class="font-weight-bold text-white">Link direto para o checkout</p>
            <p class="text-muted">Acesso direto ao checkout para campanhas.</p>
            <div class="link-row d-flex flex-wrap">
                <span class="profile-link">closyflix.com/{{ $user->username }}/checkout</span>
                <button class="btn btn-primary btn-sm ml-auto" onclick="copyToClipboard('closyflix.com/{{ $user->username }}/checkout')">Copiar</button>
            </div>
        </div> -->
    </div>
</div>

<script>
function toggleDrawer() {
    const drawer = document.getElementById('profile-drawer');
    drawer.classList.toggle('d-none');
}
</script>

<style>
/* Estilos para o drawer */
.drawer {
    background-color: #050506;
    border-radius: 12px 12px 0 0;
    max-width: 548px;
    margin: 0 auto;
    box-shadow: 0px 8px 10px -5px rgba(0, 0, 0, 0.2), 0px 16px 24px 2px rgba(0, 0, 0, 0.14), 0px 6px 30px 5px rgba(0, 0, 0, 0.12);
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1200;
    padding: 20px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
}

.icon-close {
    cursor: pointer;
    width: 24px;
    height: 24px;
    fill: #ffffff;
}

.drawer-content {
    max-width: 100%;
}

.link-row {
    display: flex;
    align-items: center;
    width: 100%;
}

.profile-link-container {
    display: flex;
    align-items: center;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 10px 15px;
    flex-grow: 1;
}

.profile-link {
    color: #e74c3c;
    font-weight: bold;
    font-size: 1rem;
    text-decoration: none;
    margin-right: auto;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-family: Arial, sans-serif;
}

.profile-link:hover {
    text-decoration: underline;
}

.copy-button {
    padding: 6px 12px;
    font-size: 0.9rem;
}

/* Ajustes responsivos para dispositivos menores */
@media (max-width: 768px) {
    .drawer {
        width: 90%;
        left: 50%;
        transform: translateX(-50%);
        padding: 10px;
    }
    .profile-link {
        font-size: 0.9rem;
    }
    .copy-button {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
}
</style>
