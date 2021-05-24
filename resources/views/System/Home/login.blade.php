<x-layout :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand" :currentUserInjected="$currentUserInjected">



    <link rel="stylesheet" href="/css/login.css?{rand}">


    <div class="login-container flex-col flex-center">
        <div class="flex-row flex-center login-row">
            <div class="flex-col flex-start pd-4x login-component-container">


                <div class="login-form-row flex-row flex-center banner-logo mgv-4px">
                    Proaction
                </div>

                <div class="login-form-row">
                    <label class="text-16 text-600">Email</label>
                    <input name="email" id="ui:login:email" class="text-16 text-400 pd-2x mgv-2x w-span" type="text" required onload="focus()" />
                    <!-- <text-input name="email" label="Email" id="ui:login:email" enableAutoComplete="true"></text-input> -->
                </div>
                <div class="login-form-row">
                    <label class="text-16 text-600">Password</label>
                    <input name="password" id="ui:login:password" class="text-16 text-400 pd-2x mgv-2x w-span" type="password" required />
                    <button type="button" class="login-pw-toggle" id="ui:login:showToggle"><i class="fas fa-eye"></i></button>
                    <!-- <pass-input name="password" label="Password" id="ui:login:password" enableAutoComplete="true"></pass-input> -->
                </div>

                <div class="login-form-row"><button type="submit" class="btn btn-admin btn-span btn-float" id="ui:login:submit">Login</button> </form>
                </div>
                <div class="login-form-row flex-row flex-between"><span class="login-subtext"><a href="/login" target="_SELF" class="login-link">Login With Pin</a></span><span class="login-subtext"><a href="/pwr" target="_SELF" class="login-link">Forgot Password?</a></span></div>

            </div>
        </div>
    </div>


    <script type="module" src="/js/Login/index.js?{rand}"></script>

</x-layout>
