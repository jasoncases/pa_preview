<x-layout :loggedIn="$loggedIn" :isAdmin="$isAdmin" :modules="$modules" :statusBox="$statusBox" :user="$user" :rand="$rand" :currentUserInjected="$currentUserInjected">
<form action="../user" method="post">
    <input type="text" name="user" id="user" value="user">
    <input type="text" name="age" id="age" value="age">
    <button type="submit">Submit</button>
</form>
</x-layout>