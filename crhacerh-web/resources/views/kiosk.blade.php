<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion des Présences - Historique | Suivi Employés</title>
    <meta name="description" content="Système de gestion des présences pour le suivi des arrivées et départs des employés.">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Itim:wght@400&family=Inter:wght@400&family=Imprima:wght@400&display=swap');

        @font-face {
            font-family: 'Irish Grover';
            font-weight: 400;
            src: local('Irish Grover'), url('https://fonts.googleapis.com/css2?family=Irish+Grover&display=swap');
        }

        *,
        :after,
        :before {
            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-pan-x: ;
            --tw-pan-y: ;
            --tw-pinch-zoom: ;
            --tw-scroll-snap-strictness: proximity;
            --tw-gradient-from-position: ;
            --tw-gradient-via-position: ;
            --tw-gradient-to-position: ;
            --tw-ordinal: ;
            --tw-slashed-zero: ;
            --tw-numeric-figure: ;
            --tw-numeric-spacing: ;
            --tw-numeric-fraction: ;
            --tw-ring-inset: ;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: rgba(59, 130, 246, .5);
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;
            --tw-blur: ;
            --tw-brightness: ;
            --tw-contrast: ;
            --tw-grayscale: ;
            --tw-hue-rotate: ;
            --tw-invert: ;
            --tw-saturate: ;
            --tw-sepia: ;
            --tw-drop-shadow: ;
            --tw-backdrop-blur: ;
            --tw-backdrop-brightness: ;
            --tw-backdrop-contrast: ;
            --tw-backdrop-grayscale: ;
            --tw-backdrop-hue-rotate: ;
            --tw-backdrop-invert: ;
            --tw-backdrop-opacity: ;
            --tw-backdrop-saturate: ;
            --tw-backdrop-sepia: ;
            --tw-contain-size: ;
            --tw-contain-layout: ;
            --tw-contain-paint: ;
            --tw-contain-style:
        }

        ::backdrop {
            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-pan-x: ;
            --tw-pan-y: ;
            --tw-pinch-zoom: ;
            --tw-scroll-snap-strictness: proximity;
            --tw-gradient-from-position: ;
            --tw-gradient-via-position: ;
            --tw-gradient-to-position: ;
            --tw-ordinal: ;
            --tw-slashed-zero: ;
            --tw-numeric-figure: ;
            --tw-numeric-spacing: ;
            --tw-numeric-fraction: ;
            --tw-ring-inset: ;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: rgba(59, 130, 246, .5);
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;
            --tw-blur: ;
            --tw-brightness: ;
            --tw-contrast: ;
            --tw-grayscale: ;
            --tw-hue-rotate: ;
            --tw-invert: ;
            --tw-saturate: ;
            --tw-sepia: ;
            --tw-drop-shadow: ;
            --tw-backdrop-blur: ;
            --tw-backdrop-brightness: ;
            --tw-backdrop-contrast: ;
            --tw-backdrop-grayscale: ;
            --tw-backdrop-hue-rotate: ;
            --tw-backdrop-invert: ;
            --tw-backdrop-opacity: ;
            --tw-backdrop-saturate: ;
            --tw-backdrop-sepia: ;
            --tw-contain-size: ;
            --tw-contain-layout: ;
            --tw-contain-paint: ;
            --tw-contain-style:
        }

        /*! tailwindcss v3.4.19 | MIT License | https://tailwindcss.com*/
        *,
        :after,
        :before {
            box-sizing: border-box;
            border: 0 solid #e5e7eb
        }

        :after,
        :before {
            --tw-content: ""
        }

        :host,
        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -moz-tab-size: 4;
            -o-tab-size: 4;
            tab-size: 4;
            font-family: ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
            font-feature-settings: normal;
            font-variation-settings: normal;
            -webkit-tap-highlight-color: transparent
        }

        body {
            margin: 0;
            line-height: inherit
        }

        hr {
            height: 0;
            color: inherit;
            border-top-width: 1px
        }

        abbr:where([title]) {
            -webkit-text-decoration: underline dotted;
            text-decoration: underline dotted
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-size: inherit;
            font-weight: inherit
        }

        a {
            color: inherit;
            text-decoration: inherit
        }

        b,
        strong {
            font-weight: bolder
        }

        code,
        kbd,
        pre,
        samp {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace;
            font-feature-settings: normal;
            font-variation-settings: normal;
            font-size: 1em
        }

        small {
            font-size: 80%
        }

        sub,
        sup {
            font-size: 75%;
            line-height: 0;
            position: relative;
            vertical-align: baseline
        }

        sub {
            bottom: -.25em
        }

        sup {
            top: -.5em
        }

        table {
            text-indent: 0;
            border-color: inherit;
            border-collapse: collapse
        }

        button,
        input,
        optgroup,
        select,
        textarea {
            font-family: inherit;
            font-feature-settings: inherit;
            font-variation-settings: inherit;
            font-size: 100%;
            font-weight: inherit;
            line-height: inherit;
            letter-spacing: inherit;
            color: inherit;
            margin: 0;
            padding: 0
        }

        button,
        select {
            text-transform: none
        }

        button,
        input:where([type=button]),
        input:where([type=reset]),
        input:where([type=submit]) {
            -webkit-appearance: button;
            background-color: transparent;
            background-image: none
        }

        :-moz-focusring {
            outline: auto
        }

        :-moz-ui-invalid {
            box-shadow: none
        }

        progress {
            vertical-align: baseline
        }

        ::-webkit-inner-spin-button,
        ::-webkit-outer-spin-button {
            height: auto
        }

        [type=search] {
            -webkit-appearance: textfield;
            outline-offset: -2px
        }

        ::-webkit-search-decoration {
            -webkit-appearance: none
        }

        ::-webkit-file-upload-button {
            -webkit-appearance: button;
            font: inherit
        }

        summary {
            display: list-item
        }

        blockquote,
        dd,
        dl,
        figure,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        hr,
        p,
        pre {
            margin: 0
        }

        fieldset {
            margin: 0
        }

        fieldset,
        legend {
            padding: 0
        }

        menu,
        ol,
        ul {
            list-style: none;
            margin: 0;
            padding: 0
        }

        dialog {
            padding: 0
        }

        textarea {
            resize: vertical
        }

        input::-moz-placeholder,
        textarea::-moz-placeholder {
            opacity: 1;
            color: #9ca3af
        }

        input::placeholder,
        textarea::placeholder {
            opacity: 1;
            color: #9ca3af
        }

        [role=button],
        button {
            cursor: pointer
        }

        :disabled {
            cursor: default
        }

        audio,
        canvas,
        embed,
        iframe,
        img,
        object,
        svg,
        video {
            display: block;
            vertical-align: middle
        }

        img,
        video {
            max-width: 100%;
            height: auto
        }

        [hidden]:where(:not([hidden=until-found])) {
            display: none
        }

        .static {
            position: static
        }

        .mx-\[3px\] {
            margin-left: 3px;
            margin-right: 3px
        }

        .-mt-\[1px\] {
            margin-top: -1px
        }

        .mb-\[170px\] {
            margin-bottom: 170px
        }

        .mb-\[20px\] {
            margin-bottom: 20px
        }

        .mb-\[32px\] {
            margin-bottom: 32px
        }

        .mb-\[3px\] {
            margin-bottom: 3px
        }

        .ml-\[10px\] {
            margin-left: 10px
        }

        .ml-\[4px\] {
            margin-left: 4px
        }

        .ml-\[6px\] {
            margin-left: 6px
        }

        .mr-\[12px\] {
            margin-right: 12px
        }

        .mr-\[18px\] {
            margin-right: 18px
        }

        .mr-\[20px\] {
            margin-right: 20px
        }

        .mr-\[8px\] {
            margin-right: 8px
        }

        .mt-\[10px\] {
            margin-top: 10px
        }

        .mt-\[18px\] {
            margin-top: 18px
        }

        .mt-\[6px\] {
            margin-top: 6px
        }

        .mt-\[8px\] {
            margin-top: 8px
        }

        .flex {
            display: flex
        }

        .h-\[1px\] {
            height: 1px
        }

        .h-\[40px\] {
            height: 40px
        }

        .h-\[8px\] {
            height: 8px
        }

        .h-auto {
            height: auto
        }

        .h-full {
            height: 100%
        }

        .min-h-screen {
            min-height: 100vh
        }

        .w-\[280px\] {
            width: 280px
        }

        .w-\[40px\] {
            width: 40px
        }

        .w-\[66\%\] {
            width: 66%
        }

        .w-\[82\%\] {
            width: 82%
        }

        .w-\[94\%\] {
            width: 94%
        }

        .w-auto {
            width: auto
        }

        .w-full {
            width: 100%
        }

        .flex-row {
            flex-direction: row
        }

        .flex-col {
            flex-direction: column
        }

        .items-start {
            align-items: flex-start
        }

        .items-end {
            align-items: flex-end
        }

        .items-center {
            align-items: center
        }

        .justify-start {
            justify-content: flex-start
        }

        .justify-center {
            justify-content: center
        }

        .gap-\[10px\] {
            gap: 10px
        }

        .gap-\[16px\] {
            gap: 16px
        }

        .gap-\[32px\] {
            gap: 32px
        }

        .self-end {
            align-self: flex-end
        }

        .rounded-\[12px\] {
            border-radius: 12px
        }

        .rounded-\[20px\] {
            border-radius: 20px
        }

        .rounded-\[28px\] {
            border-radius: 28px
        }

        .rounded-\[6px\] {
            border-radius: 6px
        }

        .border-\[5px\] {
            border-width: 5px
        }

        .border-solid {
            border-style: solid
        }

        .border-\[\#010000\] {
            --tw-border-opacity: 1;
            border-color: rgb(1 0 0/var(--tw-border-opacity, 1))
        }

        .bg-\[\#010000\] {
            --tw-bg-opacity: 1;
            background-color: rgb(1 0 0/var(--tw-bg-opacity, 1))
        }

        .bg-\[\#d9d9d926\] {
            background-color: #d9d9d926
        }

        .bg-\[\#efd6d6\] {
            --tw-bg-opacity: 1;
            background-color: rgb(239 214 214/var(--tw-bg-opacity, 1))
        }

        .bg-\[\#ffae7f\] {
            --tw-bg-opacity: 1;
            background-color: rgb(255 174 127/var(--tw-bg-opacity, 1))
        }

        .bg-\[\#ffffff\] {
            --tw-bg-opacity: 1;
            background-color: rgb(255 255 255/var(--tw-bg-opacity, 1))
        }

        .bg-\[\#ffffffa5\] {
            background-color: #ffffffa5
        }

        .object-cover {
            -o-object-fit: cover;
            object-fit: cover
        }

        .p-\[10px\] {
            padding: 10px
        }

        .p-\[4px\] {
            padding: 4px
        }

        .px-\[19px\] {
            padding-left: 19px;
            padding-right: 19px
        }

        .px-\[4px\] {
            padding-left: 4px;
            padding-right: 4px
        }

        .pb-\[12px\] {
            padding-bottom: 12px
        }

        .pl-\[30px\] {
            padding-left: 30px
        }

        .pr-\[28px\] {
            padding-right: 28px
        }

        .pt-\[12px\] {
            padding-top: 12px
        }

        .text-left {
            text-align: left
        }

        .text-\[14px\] {
            font-size: 14px
        }

        .text-\[24px\] {
            font-size: 24px
        }

        .text-\[28px\] {
            font-size: 28px
        }

        .font-normal {
            font-weight: 400
        }

        .leading-\[18px\] {
            line-height: 18px
        }

        .leading-\[30px\] {
            line-height: 30px
        }

        .leading-\[34px\] {
            line-height: 34px
        }

        .text-\[\#000000\] {
            --tw-text-opacity: 1;
            color: rgb(0 0 0/var(--tw-text-opacity, 1))
        }

        .shadow-\[0px_2px_2px_\#0000003f\] {
            --tw-shadow: 0px 2px 2px #0000003f;
            --tw-shadow-colored: 0px 2px 2px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(.4, 0, .2, 1);
            transition-duration: .15s
        }

        .hover\:scale-105:hover {
            --tw-scale-x: 1.05;
            --tw-scale-y: 1.05;
            transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y))
        }

        .hover\:shadow-lg:hover {
            --tw-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1), 0 4px 6px -4px rgba(0, 0, 0, .1);
            --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
        }

        .focus\:outline-none:focus {
            outline: 2px solid transparent;
            outline-offset: 2px
        }

        .focus\:ring-2:focus {
            --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
            --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
            box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000)
        }

        .focus\:ring-\[\#ffae7f\]:focus {
            --tw-ring-opacity: 1;
            --tw-ring-color: rgb(255 174 127/var(--tw-ring-opacity, 1))
        }

        @media (min-width:640px) {
            .sm\:mx-\[4px\] {
                margin-left: 4px;
                margin-right: 4px
            }

            .sm\:-mt-\[1px\] {
                margin-top: -1px
            }

            .sm\:mb-\[255px\] {
                margin-bottom: 255px
            }

            .sm\:mb-\[30px\] {
                margin-bottom: 30px
            }

            .sm\:mb-\[48px\] {
                margin-bottom: 48px
            }

            .sm\:mb-\[4px\] {
                margin-bottom: 4px
            }

            .sm\:ml-\[14px\] {
                margin-left: 14px
            }

            .sm\:ml-\[6px\] {
                margin-left: 6px
            }

            .sm\:ml-\[9px\] {
                margin-left: 9px
            }

            .sm\:mr-\[12px\] {
                margin-right: 12px
            }

            .sm\:mr-\[18px\] {
                margin-right: 18px
            }

            .sm\:mr-\[27px\] {
                margin-right: 27px
            }

            .sm\:mr-\[29px\] {
                margin-right: 29px
            }

            .sm\:mt-\[10px\] {
                margin-top: 10px
            }

            .sm\:mt-\[15px\] {
                margin-top: 15px
            }

            .sm\:mt-\[24px\] {
                margin-top: 24px
            }

            .sm\:mt-\[8px\] {
                margin-top: 8px
            }

            .sm\:h-\[10px\] {
                height: 10px
            }

            .sm\:w-\[330px\] {
                width: 330px
            }

            .sm\:gap-\[42px\] {
                gap: 42px
            }

            .sm\:rounded-\[16px\] {
                border-radius: 16px
            }

            .sm\:rounded-\[36px\] {
                border-radius: 36px
            }

            .sm\:border-\[7px\] {
                border-width: 7px
            }

            .sm\:p-\[13px\] {
                padding: 13px
            }

            .sm\:px-\[28px\] {
                padding-left: 28px;
                padding-right: 28px
            }

            .sm\:px-\[6px\] {
                padding-left: 6px;
                padding-right: 6px
            }

            .sm\:pb-\[15px\] {
                padding-bottom: 15px
            }

            .sm\:pl-\[40px\] {
                padding-left: 40px
            }

            .sm\:pr-\[37px\] {
                padding-right: 37px
            }

            .sm\:pt-\[15px\] {
                padding-top: 15px
            }

            .sm\:text-\[17px\] {
                font-size: 17px
            }

            .sm\:text-\[30px\] {
                font-size: 30px
            }

            .sm\:text-\[34px\] {
                font-size: 34px
            }

            .sm\:text-\[37px\] {
                font-size: 37px
            }

            .sm\:leading-\[21px\] {
                line-height: 21px
            }

            .sm\:leading-\[36px\] {
                line-height: 36px
            }

            .sm\:leading-\[37px\] {
                line-height: 37px
            }

            .sm\:leading-\[41px\] {
                line-height: 41px
            }

            .sm\:shadow-\[0px_3px_3px_\#0000003f\] {
                --tw-shadow: 0px 3px 3px #0000003f;
                --tw-shadow-colored: 0px 3px 3px var(--tw-shadow-color);
                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
            }
        }

        @media (min-width:768px) {
            .md\:mx-\[6px\] {
                margin-left: 6px;
                margin-right: 6px
            }

            .md\:-mt-\[2px\] {
                margin-top: -2px
            }

            .md\:mb-\[340px\] {
                margin-bottom: 340px
            }

            .md\:mb-\[40px\] {
                margin-bottom: 40px
            }

            .md\:mb-\[64px\] {
                margin-bottom: 64px
            }

            .md\:mb-\[6px\] {
                margin-bottom: 6px
            }

            .md\:ml-\[12px\] {
                margin-left: 12px
            }

            .md\:ml-\[18px\] {
                margin-left: 18px
            }

            .md\:ml-\[8px\] {
                margin-left: 8px
            }

            .md\:mr-\[16px\] {
                margin-right: 16px
            }

            .md\:mr-\[24px\] {
                margin-right: 24px
            }

            .md\:mr-\[36px\] {
                margin-right: 36px
            }

            .md\:mr-\[38px\] {
                margin-right: 38px
            }

            .md\:mt-\[10px\] {
                margin-top: 10px
            }

            .md\:mt-\[20px\] {
                margin-top: 20px
            }

            .md\:mt-\[30px\] {
                margin-top: 30px
            }

            .md\:h-\[12px\] {
                height: 12px
            }

            .md\:w-\[380px\] {
                width: 380px
            }

            .md\:gap-\[52px\] {
                gap: 52px
            }

            .md\:rounded-\[20px\] {
                border-radius: 20px
            }

            .md\:rounded-\[44px\] {
                border-radius: 44px
            }

            .md\:border-\[10px\] {
                border-width: 10px
            }

            .md\:p-\[16px\] {
                padding: 16px
            }

            .md\:px-\[38px\] {
                padding-left: 38px;
                padding-right: 38px
            }

            .md\:px-\[8px\] {
                padding-left: 8px;
                padding-right: 8px
            }

            .md\:pb-\[18px\] {
                padding-bottom: 18px
            }

            .md\:pl-\[50px\] {
                padding-left: 50px
            }

            .md\:pr-\[46px\] {
                padding-right: 46px
            }

            .md\:pt-\[18px\] {
                padding-top: 18px
            }

            .md\:text-\[20px\] {
                font-size: 20px
            }

            .md\:text-\[36px\] {
                font-size: 36px
            }

            .md\:text-\[40px\] {
                font-size: 40px
            }

            .md\:leading-\[25px\] {
                line-height: 25px
            }

            .md\:leading-\[42px\] {
                line-height: 42px
            }

            .md\:leading-\[44px\] {
                line-height: 44px
            }

            .md\:leading-\[49px\] {
                line-height: 49px
            }

            .md\:shadow-\[0px_4px_4px_\#0000003f\] {
                --tw-shadow: 0px 4px 4px #0000003f;
                --tw-shadow-colored: 0px 4px 4px var(--tw-shadow-color);
                box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)
            }
        }

        @media (min-width:1024px) {
            .lg\:w-\[36\%\] {
                width: 36%
            }

            .lg\:w-\[64\%\] {
                width: 64%
            }

            .lg\:flex-row {
                flex-direction: row
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body class="bg-[#efd6d6] min-h-screen">
    <main class="flex flex-row justify-start items-center w-full">
        <section class="flex flex-row justify-start items-center w-full px-[19px] sm:px-[28px] md:px-[38px] mt-[10px] sm:mt-[15px] md:mt-[20px] mb-[20px] sm:mb-[30px] md:mb-[40px]">
            <div class="flex flex-col lg:flex-row justify-start items-center w-full gap-[16px] px-[4px] sm:px-[6px] md:px-[8px]">

                <!-- Left Content Area -->
                <div class="flex flex-col justify-start items-center w-full lg:w-[64%] gap-[16px] mx-[3px] sm:mx-[4px] md:mx-[6px]">

                    <!-- Header Section -->
                    <div class="flex flex-col justify-start items-start w-full mx-[3px] sm:mx-[4px] md:mx-[6px]">
                        <div class="flex justify-start w-full items-baseline">
                            <h1 class="text-[24px] sm:text-[30px] md:text-[36px] font-normal leading-[30px] sm:text-[37px] md:leading-[44px] text-left text-[#000000]" style="font-family: 'Itim', cursive;">
                                Système de Gestion des Présences
                            </h1>
                        </div>
                        <p class="text-[14px] sm:text-[17px] md:text-[20px] font-normal leading-[18px] sm:leading-[21px] md:leading-[25px] text-left text-[#000000] mt-[8px] sm:mt-[10px]" style="font-family: 'Inter', sans-serif;">
                            Suivi des arrivées et départs des employés
                        </p>
                    </div>

                    <!-- History Panel -->
                    <div class="flex flex-col justify-start items-start w-full bg-[#ffffff] border-[5px] sm:border-[7px] md:border-[10px] border-solid border-[#010000] rounded-[28px] sm:rounded-[36px] md:rounded-[44px] pt-[12px] sm:pt-[15px] md:pt-[18px] pr-[28px] sm:pr-[37px] md:pr-[46px] pb-[12px] sm:pb-[15px] md:pb-[18px] pl-[30px] sm:pl-[40px] md:pl-[50px] ml-[6px] sm:ml-[9px] md:ml-[12px]">

                        <h2 class="text-[28px] sm:text-[34px] md:text-[40px] font-normal leading-[34px] sm:leading-[41px] md:leading-[49px] text-left text-[#000000]" style="font-family: 'Irish Grover', cursive;">
                            Historique
                        </h2>

                        <!-- Divider Line -->
                        <div class="w-[94%] h-[1px] bg-[#010000] rounded-[6px] -mt-[1px] sm:-mt-[1px] md:-mt-[2px]"></div>

                        <!-- Attendance Records List -->
                        <div id="pointages-list" class="flex flex-col gap-[40px] w-full mt-[12px] sm:mt-[18px] md:mt-[24px] mb-[30px]">
                            <p class="text-center py-10 text-slate-500">Chargement du flux en direct...</p>
                        </div>
                    </div>
                </div>

                <!-- Right Content Area: QR Code & Progress -->
                <div class="flex flex-col gap-[32px] sm:gap-[42px] md:gap-[52px] justify-start items-end self-end w-full lg:w-[36%] mb-[32px] sm:mb-[48px] md:mb-[64px]">

                    <!-- QR Code Container -->
                    <div id="qr-code-wrapper" class="flex flex-row justify-center items-center w-auto bg-[#ffffff] border-[5px] sm:border-[7px] md:border-[10px] border-solid border-[#010000] rounded-[28px] sm:rounded-[36px] md:rounded-[44px] p-[10px] sm:p-[13px] md:p-[16px] shadow-[0px_2px_2px_#0000003f] sm:shadow-[0px_3px_3px_#0000003f] md:shadow-[0px_4px_4px_#0000003f] transition-all hover:shadow-lg">
                        <div id="qrcode" class="w-[280px] sm:w-[330px] md:w-[380px] h-[280px] sm:h-[330px] md:h-[380px]"></div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="flex flex-row justify-start items-center w-[82%] bg-[#ffffffa5] rounded-[6px] mr-[18px] sm:mr-[27px] md:mr-[36px]">
                        <div id="progress-bar-fill" class="w-full h-[8px] sm:h-[10px] md:h-[12px] bg-[#010000] rounded-[6px] transition-none" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                </div>

            </div>
        </section>
    </main>

    <script>
        // 1. Gestion du QR Code et de la barre de progression
        const QR_DURATION = 30000; // 30 secondes
        let startTime = Date.now();
        const progressBar = document.getElementById('progress-bar-fill');
        const qrcodeContainer = document.getElementById('qrcode');

        // Initialisation du générateur QR
        const qrcode = new QRCode(qrcodeContainer, {
            width: 380,
            height: 380,
            colorDark: "#010000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        function generateNewQR() {
            // Génère un jeton unique basé sur l'identifiant de borne et le timestamp
            const token = `CHRACERH-KIOSK-01-${Date.now()}`;
            qrcode.makeCode(token);
        }

        function updateCycle() {
            const now = Date.now();
            const elapsed = now - startTime;
            const progress = Math.max(0, 100 - (elapsed / QR_DURATION) * 100);

            progressBar.style.width = progress + '%';

            if (elapsed >= QR_DURATION) {
                startTime = now;
                generateNewQR();
            }
            requestAnimationFrame(updateCycle);
        }

        // 2. Récupération des pointages (Historique)
        async function fetchRecentPointages() {
            try {
                const response = await fetch('/api/kiosk/recent-pointages');
                const data = await response.json();
                const container = document.getElementById('pointages-list');

                if (data.length === 0) {
                    container.innerHTML = `<p class="text-center py-10 text-slate-500">Aucun pointage aujourd'hui.</p>`;
                    return;
                }

                const htmlContent = data.map(p => {
                    const label = p.type === 'ENTREE' ? 'Arrivé' : 'Départ';
                    const borderColor = p.type === 'ENTREE' ? 'border-l-4 border-green-500' : 'border-l-4 border-orange-500';
                    return `
            <div class="flex flex-row justify-between items-center w-full bg-[#d9d9d926] rounded-[12px] sm:rounded-[16px] md:rounded-[20px] ${borderColor} shadow-sm">
              <div class="flex flex-row justify-start items-center py-[12px] ml-[6px] sm:ml-[8px] md:ml-[10px]">
                <div class="w-[48px] h-[48px] bg-[#ffae7f] rounded-full flex items-center justify-center transition-all hover:scale-105 overflow-hidden border-2 border-[#010000]">
                  <img src="https://ui-avatars.com/api/?name=${p.first_name}+${p.last_name}&background=random" alt="Avatar" class="w-full h-full rounded-full object-cover" />
                </div>
                <p class="text-[18px] sm:text-[22px] md:text-[24px] font-normal text-[#000000] ml-[10px] sm:ml-[14px] md:ml-[18px]" style="font-family: 'Imprima', sans-serif;">
                  <span class="font-semibold">${p.first_name}</span> <span>${p.last_name}</span>
                </p>
              </div>
              <div class="text-right mr-[12px] sm:mr-[18px] md:mr-[24px]">
                <p class="text-[18px] sm:text-[22px] md:text-[24px] font-normal text-[#000000]" style="font-family: 'Imprima', sans-serif;">
                  ${label} à <span class="font-bold">${p.time}</span>
                </p>
              </div>
            </div>
          `;
                }).join('');

                // On ne met à jour que si le contenu a changé pour éviter le clignotement
                if (container.innerHTML !== htmlContent) {
                    container.innerHTML = htmlContent;
                }
            } catch (error) {
                console.error("Erreur :", error);
            }
        }

        // Lancement
        generateNewQR();
        requestAnimationFrame(updateCycle);
        fetchRecentPointages();
        setInterval(fetchRecentPointages, 5000);
    </script>
</body>

</html>