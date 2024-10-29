<link href="https://vjs.zencdn.net/7.14.3/video-js.css" rel="stylesheet" />
<script src="https://vjs.zencdn.net/7.14.3/video.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

@php
    // Define o domínio do CDN e do link direto
    $cdnUrl = 'https://closyflix.nyc3.cdn.digitaloceanspaces.com';
    $directUrl = 'https://closyflix.nyc3.digitaloceanspaces.com';

    // Substitui o domínio direto pelo CDN
    $videoPath = str_replace($directUrl, $cdnUrl, $attachment->path);

    // Verifica se o vídeo é no formato HLS (.m3u8)
    $isHls = strpos($videoPath, '.m3u8') !== false;
    if ($isHls) {
        $videoPath = str_replace('/hls.m3u8', '/hls.m3u8/master.m3u8', $videoPath);
    }

    // Caminho para a thumbnail
    $thumbnailPath = pathinfo($attachment->path, PATHINFO_FILENAME);
@endphp

@if($isGallery)
    @if(AttachmentHelper::getAttachmentType($attachment->type) == 'image')
        <img src="{{$videoPath}}" draggable="false" alt="" class="img-fluid rounded-0 w-100">
    @elseif(AttachmentHelper::getAttachmentType($attachment->type) == 'video')
    <div class="video-wrapper h-100-target w-100 d-flex justify-content-center align-items-center">
    @if($isHls)
        {{-- Prioriza HLS --}}
        <div id="thumbnail-wrapper-{{$attachment->id}}" class="video-thumbnail h-100-target w-100" style="position: relative; cursor: pointer;">
            <img id="thumbnail-{{$attachment->id}}" src="{{ Storage::url('posts/videos/thumbnails/' . $thumbnailPath . '.jpg') }}" class="img-fluid h-100-target w-100" alt="thumbnail" style="object-fit: cover;" loading="lazy">
            <div class="play-button" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                <img src="{{ asset('img/IconeRep.png') }}" alt="Play" style="width: 50px; height: 50px;">
            </div>
        </div>

        <video id="hls-player-{{$attachment->id}}" class="video-js vjs-default-skin w-100" controls style="display: none;" poster="{{ Storage::url('posts/videos/thumbnails/' . $thumbnailPath . '.jpg') }}">
            <!-- Nenhuma fonte é definida inicialmente para o vídeo -->
        </video>

        <script>
            document.getElementById('thumbnail-wrapper-{{$attachment->id}}').addEventListener('click', function() {
                var thumbnailWrapper = document.getElementById('thumbnail-wrapper-{{$attachment->id}}');
                var video = document.getElementById('hls-player-{{$attachment->id}}');

                // Esconde a thumbnail e mostra o vídeo
                thumbnailWrapper.style.display = 'none';
                video.style.display = 'block';

                // Atribui o src do vídeo somente após o clique
                if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = "{{$videoPath}}";
                } else if (Hls.isSupported()) {
                    var hls = new Hls();
                    hls.loadSource("{{$videoPath}}");
                    hls.attachMedia(video);
                } else {
                    console.error('HLS não suportado neste navegador.');
                }

                // Reproduz o vídeo após o carregamento
                video.play();
            });
        </script>
    @else
        {{-- Fallback para MP4 --}}
        <div id="thumbnail-wrapper-{{$attachment->id}}" class="video-thumbnail h-100-target w-100" style="position: relative; cursor: pointer;">
            <img id="thumbnail-{{$attachment->id}}" src="{{ Storage::url('posts/videos/thumbnails/' . $thumbnailPath . '.jpg') }}" class="img-fluid h-100-target w-100" alt="thumbnail" style="object-fit: cover;" loading="lazy">
            <div class="play-button" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                <img src="{{ asset('img/IconeRep.png') }}" alt="Play" style="width: 50px; height: 50px;">
            </div>
        </div>

        <!-- O vídeo inicialmente fica escondido -->
        <video id="mp4-player-{{$attachment->id}}" class="video-preview w-100" controls controlsList="nodownload" style="display: none;" poster="{{ Storage::url('posts/videos/thumbnails/' . $thumbnailPath . '.jpg') }}">
            <source src="{{$videoPath}}#t=0.001" type="video/mp4">
        </video>

        <script>
            document.getElementById('thumbnail-wrapper-{{$attachment->id}}').addEventListener('click', function() {
                var thumbnailWrapper = document.getElementById('thumbnail-wrapper-{{$attachment->id}}');
                var video = document.getElementById('mp4-player-{{$attachment->id}}');

                // Esconde a thumbnail e mostra o vídeo
                thumbnailWrapper.style.display = 'none';
                video.style.display = 'block';

                // Reproduz o vídeo
                video.play();
            });
        </script>
    @endif
</div>

@elseif(AttachmentHelper::getAttachmentType($attachment->type) == 'audio')
    <div class="video-wrapper h-100-target w-100 d-flex justify-content-center align-items-center">
        <audio class="video-preview w-75" src="{{$videoPath}}#t=0.001" controls controlsList="nodownload"></audio>
    </div>
@endif

@else
    @if(AttachmentHelper::getAttachmentType($attachment->type) == 'image')
        <img src="{{$videoPath}}" draggable="false" alt="" class="img-fluid rounded-0 w-100">
    @elseif(AttachmentHelper::getAttachmentType($attachment->type) == 'video')
        <div class="video-wrapper h-100-target w-100 d-flex justify-content-center align-items-center">
            @if($isHls)
                {{-- Prioriza HLS --}}
                <div id="thumbnail-wrapper-{{$attachment->id}}" class="video-thumbnail h-100-target w-100" style="position: relative; cursor: pointer;">
                    <img id="thumbnail-{{$attachment->id}}" src="{{ Storage::url('posts/videos/thumbnails/' . $thumbnailPath . '.jpg') }}" class="img-fluid h-100-target w-100" alt="thumbnail" style="object-fit: cover;" loading="lazy">
                    <div class="play-button" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <img src="{{ asset('img/IconeRep.png') }}" alt="Play" style="width: 50px; height: 50px;">
                    </div>
                </div>

                <video id="hls-player-{{$attachment->id}}" class="video-js vjs-default-skin w-100" controls style="display: none;"></video>
                <script>
                    document.getElementById('thumbnail-wrapper-{{$attachment->id}}').addEventListener('click', function() {
                        var thumbnailWrapper = document.getElementById('thumbnail-wrapper-{{$attachment->id}}');
                        var video = document.getElementById('hls-player-{{$attachment->id}}');

                        thumbnailWrapper.style.display = 'none';
                        video.style.display = 'block';

                        if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            video.src = "{{$videoPath}}";
                        } else if (Hls.isSupported()) {
                            var hls = new Hls();
                            hls.loadSource("{{$videoPath}}");
                            hls.attachMedia(video);
                        }

                        video.play();
                    });
                </script>
            @else
                {{-- Fallback para MP4 --}}
                <div id="thumbnail-wrapper-{{$attachment->id}}" class="video-thumbnail h-100-target w-100" style="position: relative; cursor: pointer;">
                    <img id="thumbnail-{{$attachment->id}}" src="{{ Storage::url('posts/videos/thumbnails/' . $thumbnailPath . '.jpg') }}" class="img-fluid h-100-target w-100" alt="thumbnail" style="object-fit: cover;" loading="lazy">
                    <div class="play-button" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <img src="{{ asset('img/IconeRep.png') }}" alt="Play" style="width: 50px; height: 50px;">
                    </div>
                </div>

                <video id="mp4-player-{{$attachment->id}}" class="video-preview w-100" controls controlsList="nodownload" style="display: none;">
                    <source src="{{$videoPath}}#t=0.001" type="video/mp4">
                </video>

                <script>
                    document.getElementById('thumbnail-wrapper-{{$attachment->id}}').addEventListener('click', function() {
                        var thumbnailWrapper = document.getElementById('thumbnail-wrapper-{{$attachment->id}}');
                        var video = document.getElementById('mp4-player-{{$attachment->id}}');

                        thumbnailWrapper.style.display = 'none';
                        video.style.display = 'block';

                        video.play();
                    });
                </script>
            @endif
        </div>
    @elseif(AttachmentHelper::getAttachmentType($attachment->type) == 'audio')
        <div class="video-wrapper h-100-target w-100 d-flex justify-content-center align-items-center">
            <audio class="video-preview w-75" src="{{$videoPath}}#t=0.001" controls controlsList="nodownload"></audio>
        </div>
    @endif
@endif
