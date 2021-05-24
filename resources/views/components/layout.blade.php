<x-header :rand="$rand"></x-header>

<x-shell :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand"></x-shell>

{{$slot}}

<x-footer :rand="$rand" :currentUserInjected="$currentUserInjected"></x-footer>