{{--
    Webtech-solutions 2025, All rights reserved.
    Email Template Preview - renders markdown content with mail styling
--}}

@php
    // Simulate mail layout for preview
    $theme = file_get_contents(resource_path('views/vendor/mail/html/themes/vilagmuhely.css'));
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        {!! $theme !!}

        /* Preview-specific overrides */
        body {
            padding: 20px;
        }
        .wrapper {
            background: transparent;
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header">
                            <span style="color: #ef4444; font-size: 32px; margin-right: 8px;">♠</span>
                            <span style="color: #f1f5f9; font-size: 28px; font-weight: bold; letter-spacing: -0.5px;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        {!! $content !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell" align="center">
                                        <p style="color: #475569; font-size: 18px; letter-spacing: 8px; margin: 15px 0;">♠ ♥ ♣ ♦</p>
                                        <p style="color: #64748b; font-size: 13px; margin: 8px 0;">
                                            © {{ date('Y') }} {{ config('app.name') }}, All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
