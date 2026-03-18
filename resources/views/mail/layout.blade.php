<!DOCTYPE html>
<html lang="en">
    <body style="margin:0; padding:0; background-" class="text-secondary">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; background- margin:0; padding:24px 0" class="text-secondary">
            <tr>
                <td align="center" style="padding:24px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; border-collapse:separate; background- border:1px solid #e5e5e5; border-radius:12px" class="text-secondary">
                        <tr>
                            <td style="padding:28px 32px 16px 32px; font-family:Arial, Helvetica, sans-serif; font-size:20px; line-height:28px; font-weight:600; text-align:left" class="text-secondary">
                                Glitter
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 8px 32px; font-family:Arial, Helvetica, sans-serif; font-size:24px; line-height:32px; font-weight:600; text-align:left" class="text-secondary">
                                @yield('title')
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 24px 32px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; text-align:left" class="text-secondary">
                                @yield('content')
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 32px 32px; text-align:left;">
                                <a href="@yield('action_url')" style="display:inline-block; background- color:#ffffff; padding:12px 20px; border-radius:6px; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px; font-weight:600" class="text-secondary">
                                    @yield('action_text')
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:16px 32px 28px 32px; border-top:1px solid #e5e5e5; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:20px; text-align:left" class="text-secondary">
                                This is an automated message from Glitter.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
