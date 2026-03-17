<!DOCTYPE html>
<html lang="en">
    <body style="margin:0; padding:0; background-color:#f5f5f5;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border-collapse:collapse; background-color:#f5f5f5; margin:0; padding:24px 0;">
            <tr>
                <td align="center" style="padding:24px 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; border-collapse:separate; background-color:#ffffff; border:1px solid #e5e5e5; border-radius:12px;">
                        <tr>
                            <td style="padding:28px 32px 16px 32px; font-family:Arial, Helvetica, sans-serif; font-size:20px; line-height:28px; font-weight:600; color:#111111; text-align:left;">
                                Glitter
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 8px 32px; font-family:Arial, Helvetica, sans-serif; font-size:24px; line-height:32px; font-weight:600; color:#111111; text-align:left;">
                                @yield('title')
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 24px 32px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; color:#444444; text-align:left;">
                                @yield('message')
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 32px 32px 32px; text-align:left;">
                                <a href="@yield('action_url')" style="display:inline-block; background-color:#111111; color:#ffffff; padding:12px 20px; border-radius:6px; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px; font-weight:600;">
                                    @yield('action_text')
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:16px 32px 28px 32px; border-top:1px solid #e5e5e5; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:20px; color:#666666; text-align:left;">
                                This is an automated message from Glitter.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
