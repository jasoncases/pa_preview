</div>
<!-- <div class="footer"> -->
<!--Include Footer Nav? -->
<!-- </div> -->
<div class="modal-blocker" data-id="modal-blocker">

</div>
<div id="snapshot-anchor" style="height:0;width:0;position:absolute;left:100%;top:0">
    <x-snapshot></x-snapshot>
</div>
<div class="paLoader" id="ui:system:loader">
    <div class="loader-spinner">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
</div>

<input type="hidden" data-id="serializedCurrentUser" value="{{$currentUserInjected}}" />

<!-- <script type="text/javascript" src="js/auth.js?{{$rand}}"></script> -->
<script type="module" src="/js/Voice/TestVoiceAnnouncement.js"></script>
<script type="module" src="/js/Dashboard/index.js?{{$rand}}?{{$rand}}"></script>

<script type="module">
    import {detectClient} from '/js/System/Lib/Lib.js'
    function detectExtension() {
        try {
            if (detectClient() !== 'chrome') {
                throw 'Chrome browser not found. Bypassing Chrome extension.';
            }
            var img;
            img = new Image();
            img.src = "chrome-extension://bojddmaledboibofdjoaciaaclnkkhnh/test.png";
            img.onload = function () {
                //callback(true);
                console.log('extension installed');
            };
            img.onerror = function () {
                //callback(false);
                console.log('extension NOT installed');
                let script = document.createElement('script');
                script.type = 'module';
                script.src = '/js/Voice/non_index.js';
                document.body.appendChild(script);
            };
        } catch( e) {
            console.error(e)
        }
    }

    detectExtension();
</script>


<!-- <script type="text/javascript" src="/js/Voice.js?{{$rand}}?{{$rand}}"></script> -->


<!-- Late dependency modules go below this line -->
<script type="module" src="/js/globalPrimatives.js?{{$rand}}?{{$rand}}"></script>
<script type="module" src="/js/Timesheets/index.js?{{$rand}}"></script>
<!-- <script type="module" src="/js/Voice/non_index.js?{{$rand}}"></script> -->
<script type="module" src="/js/UserDropdown/index.js?{{$rand}}"></script>
<script type="module" src="/js/User/index.js?{{$rand}}"></script>
<script type="module" src="/js/Proaction/index.js?{{$rand}}"></script>



<script type="module" src="/js/Home/footer.js"></script>
