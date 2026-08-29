<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bmchess-root bmchess-header-root">
  <div class="page">
    <header class="topbar">
      <nav class="quick-bar" data-i18n-aria="quick.aria" aria-label="Quick games">
        <button type="button" id="quick-train" class="quick-btn is-primary" data-i18n="newgame.menu" data-i18n-title="quick.trainHint" title="Training from the starting position">Start a new game</button>
        <button type="button" id="quick-train-12" class="quick-btn" data-i18n="quick.train12" data-i18n-title="quick.train12Hint" title="Training after 12 moves">12 moves</button>
        <button type="button" id="quick-train-24" class="quick-btn" data-i18n="quick.train24" data-i18n-title="quick.train24Hint" title="Training after 24 moves">24 moves</button>
        <button type="button" id="btn-training-mode" class="quick-btn" data-i18n="train.mode" data-i18n-title="train.modeHint" title="First you only see the moves. After you pick one, scores, labels and hearts appear." aria-pressed="false">Training mode</button>
        <button type="button" id="quick-online" class="quick-btn" data-i18n="tab.online" data-i18n-title="quick.onlineHint" title="Play online">Play online</button>
        <button type="button" id="quick-settings" class="quick-btn" data-i18n="tab.settings" data-i18n-title="quick.settingsHint" title="Open settings">Settings</button>
      </nav>
      <div class="app-menu">
        <button type="button" id="btn-menu" class="app-menu-btn" data-i18n-aria="menu.aria" aria-label="Menu" aria-expanded="false" aria-controls="app-menu-panel">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M4 7h16v2H4V7zm0 4h16v2H4v-2zm0 4h16v2H4v-2z"/></svg>
        </button>
        <div id="app-menu-panel" class="app-menu-panel" hidden>
          <p class="app-menu-label" data-i18n="lang.group">Language</p>
          <div class="lang-switch" role="group" data-i18n-aria="lang.group" aria-label="Language">
            <button type="button" id="btn-lang-it" data-lang="it" data-i18n-aria="lang.it" aria-label="Italiano" onclick="return setChessLang('it')">
              <svg class="lang-flag" viewBox="0 0 3 2" aria-hidden="true"><rect width="1" height="2" fill="#009246"/><rect x="1" width="1" height="2" fill="#fff"/><rect x="2" width="1" height="2" fill="#ce2b37"/></svg>
              IT
            </button>
            <button type="button" id="btn-lang-en" class="is-on" data-lang="en" data-i18n-aria="lang.en" aria-label="English" onclick="return setChessLang('en')">
              <svg class="lang-flag" viewBox="0 0 60 30" aria-hidden="true">
                <clipPath id="uk-flag"><path d="M0 0h60v30H0z"/></clipPath>
                <g clip-path="url(#uk-flag)">
                  <path d="M0 0h60v30H0z" fill="#012169"/>
                  <path d="M0 0l60 30M60 0L0 30" stroke="#fff" stroke-width="6"/>
                  <path d="M0 0l60 30M60 0L0 30" stroke="#C8102E" stroke-width="4"/>
                  <path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10"/>
                  <path d="M30 0v30M0 15h60" stroke="#C8102E" stroke-width="6"/>
                </g>
              </svg>
              EN
            </button>
          </div>
          <button type="button" id="btn-menu-new" class="app-menu-new" data-i18n="newgame.menu">Start a new game</button>
          <button type="button" id="btn-menu-settings" class="app-menu-settings" data-i18n="tab.settings">Settings</button>
        </div>
      </div>
    </header>
  </div>
</div>
