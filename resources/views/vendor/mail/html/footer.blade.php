<tr>
<td style="background: rgba(0, 0, 0, 0.2); padding: 32px 0; border-top: 1px solid rgba(255, 255, 255, 0.1);">
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Brand Section -->
<tr>
<td style="padding: 0 16px 24px 16px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td>
<a href="{{ config('app.url') }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; margin-bottom: 12px;">
<span style="font-size: 24px; line-height: 1;">💎</span>
<span style="font-size: 20px; font-weight: bold; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
Világműhely
</span>
</a>
<p style="font-size: 12px; color: #9ca3af; margin: 8px 0 0 0; line-height: 1.5;">
Where creativity crystallizes into something beautiful.
</p>
</td>
</tr>
</table>
</td>
</tr>

<!-- Links Section -->
<tr>
<td style="padding: 0 16px 24px 16px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="25%" valign="top" style="padding-right: 8px;">
<p style="font-weight: bold; font-size: 14px; color: #ffffff; margin: 0 0 12px 0;">Explore</p>
<p style="margin: 0; padding: 0;">
<a href="{{ config('app.url') }}/crystals" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Crystal Gallery</a>
<a href="{{ config('app.url') }}/admin" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Dashboard</a>
</p>
</td>
<td width="25%" valign="top" style="padding-right: 8px;">
<p style="font-weight: bold; font-size: 14px; color: #ffffff; margin: 0 0 12px 0;">Resources</p>
<p style="margin: 0; padding: 0;">
<a href="{{ config('app.url') }}/changelog" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Change Log</a>
<a href="mailto:info@webtech-solutions.hu" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Help Center</a>
<a href="https://discord.gg/QJAcDyjA" target="_blank" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Community</a>
</p>
</td>
<td width="25%" valign="top" style="padding-right: 8px;">
<p style="font-weight: bold; font-size: 14px; color: #ffffff; margin: 0 0 12px 0;">Connect</p>
<p style="margin: 0; padding: 0;">
<a href="https://www.facebook.com/profile.php?id=61575724097365" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Facebook</a>
<a href="https://discord.gg/QJAcDyjA" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Discord</a>
<a href="https://www.tiktok.com/@vilagmuhely" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">TikTok</a>
<a href="https://www.instagram.com/vilagmuhely/" style="display: block; font-size: 12px; color: #9ca3af; text-decoration: none; margin-bottom: 6px;">Instagram</a>
</p>
</td>
<td width="25%" valign="top">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>

<!-- Copyright Section -->
<tr>
<td style="padding: 24px 16px 0 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
<p style="text-align: center; font-size: 12px; color: #9ca3af; margin: 0;">
&copy; {{ date('Y') }} Világműhely. Operated by Webtech Solutions
</p>
</td>
</tr>
</table>
</td>
</tr>
