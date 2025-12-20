@props(['url'])
<tr>
<td class="header" style="background: linear-gradient(to bottom right, #1f2937, #581c87, #312e81); padding: 20px 0; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
<a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; color: #ffffff;">
<span style="font-size: 24px; line-height: 1;">💎</span>
<span style="font-size: 20px; font-weight: bold; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
{{ config('app.name', 'Világműhely') }}
</span>
</a>
</td>
</tr>
