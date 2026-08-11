<?php
declare(strict_types=1);
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#5d3b8c">
    <meta name="description" content="A playful choose-your-own-adventure invitation made for one memorable day.">
    <title>Two Paths, One Adventure</title>

    <link rel="manifest" href="manifest.webmanifest">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/icon-192.png">
    <link rel="apple-touch-icon" href="assets/icons/icon-192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.0">
</head>
<body>
    <div class="ambient ambient-one" aria-hidden="true"></div>
    <div class="ambient ambient-two" aria-hidden="true"></div>
    <div class="ambient ambient-three" aria-hidden="true"></div>
    <div id="floatingBits" class="floating-bits" aria-hidden="true"></div>

    <header class="app-header">
        <div class="container-xl d-flex align-items-center justify-content-between gap-3">
            <button class="brand-button" type="button" id="brandButton" aria-label="Return to welcome screen">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 48 48" role="img">
                        <path d="M7 12c7 1 12 5 17 12 5-7 10-11 17-12-1 12-7 21-17 29C14 33 8 24 7 12Z" fill="currentColor"/>
                        <path d="M24 24v17" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>
                    <strong>Two Paths</strong>
                    <small>One Adventure</small>
                </span>
            </button>

            <div class="header-actions">
                <button id="soundToggle" class="icon-button" type="button" aria-label="Turn on ambient sound" title="Ambient sound">
                    <span class="sound-off-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 9v6h4l5 4V5L8 9H4Zm13.5 3a4.5 4.5 0 0 0-2.2-3.9v7.8a4.5 4.5 0 0 0 2.2-3.9Z"/></svg>
                    </span>
                </button>

                <button id="themeToggle" class="icon-button" type="button" aria-label="Switch theme" title="Switch theme">
                    <span class="theme-icon" aria-hidden="true"></span>
                </button>

                <button id="installButton" class="install-button d-none" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 3h2v10.2l3.6-3.6 1.4 1.4-6 6-6-6 1.4-1.4 3.6 3.6V3ZM5 19h14v2H5v-2Z"/></svg>
                    Install
                </button>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="container-xl">
            <section id="welcomeScreen" class="screen active-screen" aria-labelledby="welcomeTitle">
                <div class="welcome-layout">
                    <div class="welcome-copy">
                        <div class="mini-label">
                            <span class="mini-label-dot"></span>
                            A tiny plan with a little mystery
                        </div>
                        <h1 id="welcomeTitle">Every great adventure starts with one small choice.</h1>
                        <p class="lead-copy">Ready to create ours?</p>
                        <p class="welcome-note">
                            Five quick choices. No wrong answers. At the end, your perfect day appears.
                        </p>

                        <form id="welcomeForm" class="welcome-form" novalidate>
                            <label for="guestName" class="form-label">What should I call you?</label>
                            <div class="name-row">
                                <input
                                    id="guestName"
                                    name="guest_name"
                                    class="form-control custom-input"
                                    type="text"
                                    maxlength="60"
                                    autocomplete="name"
                                    placeholder="Your name"
                                    required
                                >
                                <button class="primary-button" type="submit">
                                    Start Adventure
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                                </button>
                            </div>
                            <div class="invalid-feedback d-block" id="nameError" aria-live="polite"></div>
                        </form>
                    </div>

                    <div class="welcome-art" aria-hidden="true">
                        <div class="route-card">
                            <div class="route-top">
                                <span class="route-number">01</span>
                                <span class="route-caption">Your route</span>
                            </div>
                            <div class="route-map">
                                <span class="route-point point-a"></span>
                                <span class="route-point point-b"></span>
                                <span class="route-point point-c"></span>
                                <span class="route-point point-d"></span>
                                <svg viewBox="0 0 420 300" preserveAspectRatio="none">
                                    <path d="M28 245 C120 270, 116 102, 205 132 S292 232, 392 52"
                                          fill="none" stroke="currentColor" stroke-width="3.5"
                                          stroke-linecap="round" stroke-dasharray="8 11"/>
                                </svg>
                                <div class="route-stamp">
                                    <span>Made for</span>
                                    <strong>a good story</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="gameScreen" class="screen" aria-labelledby="questionTitle">
                <div class="game-shell">
                    <div class="progress-area">
                        <div class="progress-meta">
                            <span id="stepLabel">Choice 1 of 5</span>
                            <span id="progressPercent">20%</span>
                        </div>
                        <div class="progress custom-progress" role="progressbar" aria-label="Adventure progress" aria-valuemin="0" aria-valuemax="100">
                            <div id="progressBar" class="progress-bar"></div>
                        </div>
                    </div>

                    <div class="question-panel">
                        <button id="backButton" class="back-button" type="button">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg>
                            Back
                        </button>

                        <div class="question-heading">
                            <span id="questionEyebrow" class="question-eyebrow"></span>
                            <h2 id="questionTitle"></h2>
                            <p id="questionDescription"></p>
                        </div>

                        <div id="optionsGrid" class="options-grid" aria-live="polite"></div>
                    </div>
                </div>
            </section>

            <section id="summaryScreen" class="screen" aria-labelledby="summaryTitle">
                <div class="summary-shell">
                    <div class="summary-card">
                        <span class="summary-kicker">Your route is ready</span>
                        <h2 id="summaryTitle">This feels like a day worth remembering.</h2>
                        <p id="summaryText" class="summary-text"></p>

                        <div id="summaryChips" class="summary-chips"></div>

                        <div class="invitation-box">
                            <span class="invitation-line"></span>
                            <p>The adventure is planned. Now I only need the right person to join me.</p>
                            <span class="invitation-line"></span>
                        </div>

                        <div class="summary-actions">
                            <button id="acceptButton" class="primary-button celebration-button" type="button">
                                Let’s Make It Happen
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                            </button>
                            <button id="restartButton" class="secondary-button" type="button">
                                I Need Another Adventure
                            </button>
                        </div>
                    </div>

                    <aside class="ticket-card" aria-label="Adventure ticket">
                        <div class="ticket-notch notch-left"></div>
                        <div class="ticket-notch notch-right"></div>
                        <span class="ticket-label">ADVENTURE PASS</span>
                        <strong id="ticketGuest">Guest</strong>
                        <div class="ticket-details">
                            <span><small>Stops</small><b>5</b></span>
                            <span><small>Rules</small><b>None</b></span>
                            <span><small>Mood</small><b>Good</b></span>
                        </div>
                        <div class="ticket-code" id="ticketCode">PATH-0000</div>
                    </aside>
                </div>
            </section>

            <section id="acceptedScreen" class="screen" aria-labelledby="acceptedTitle">
                <div class="accepted-shell">
                    <div class="accepted-symbol" aria-hidden="true">
                        <svg viewBox="0 0 64 64">
                            <path d="M12 33 26 47 53 19" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="summary-kicker">It’s a plan</span>
                    <h2 id="acceptedTitle">Best answer of the day.</h2>
                    <p>Your choices are saved. The rest of the story belongs outside this screen.</p>

                    <div class="accepted-actions">
                        <button id="shareButton" class="primary-button" type="button">
                            Share Our Adventure
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a3 3 0 1 0-2.8-4 3 3 0 0 0 .1 2.4L8.8 10a3 3 0 1 0 0 4l6.5 3.6A3 3 0 1 0 16.2 16l-6.5-3.6v-.8l6.5-3.6c.5.6 1.1 1 1.8 1Z"/></svg>
                        </button>
                        <button id="newAdventureButton" class="secondary-button" type="button">Create Another Route</button>
                    </div>
                </div>
            </section>

            <section id="errorScreen" class="screen" aria-labelledby="errorTitle">
                <div class="error-shell">
                    <span class="error-mark">!</span>
                    <h2 id="errorTitle">The path took a small detour.</h2>
                    <p id="errorMessage">Please check your connection and try again.</p>
                    <button id="retryButton" class="primary-button" type="button">Try Again</button>
                </div>
            </section>
        </div>
    </main>

    <div id="toast" class="app-toast" role="status" aria-live="polite"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
    <script src="assets/js/app.js?v=1.0.0"></script>
    <script src="assets/js/pwa.js?v=1.0.0"></script>
</body>
</html>
