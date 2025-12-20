# Email Header & Footer Redesign

## Overview
Redesign email headers and footers to match the homepage design with modern glassmorphism aesthetic, gradient colors, and crystal theme branding.

## Current State
- Basic Laravel default email templates
- Simple white background with minimal styling
- Generic branding (Laravel logo placeholder)
- No visual connection to Világműhely brand
- Plain footer with basic copyright text

## Target Design
Match homepage design (`resources/views/welcome.blade.php`):
- **Color Scheme**: Purple/cyan/pink gradient (`from-purple-600 to-cyan-600`, `from-purple-900 via-purple-900 to-indigo-900`)
- **Branding**: 💎 Crystal emoji + "Világműhely" text logo
- **Style**: Modern glassmorphism with backdrop blur effects
- **Typography**: Bold, clean sans-serif matching homepage
- **Footer**: Consistent with homepage footer design

## Technical Requirements

### Files to Modify
1. `resources/views/vendor/mail/html/header.blade.php` - Email header component
2. `resources/views/vendor/mail/html/footer.blade.php` - Email footer component
3. `resources/views/vendor/mail/html/themes/default.css` - Email theme styles

### Header Design Specifications
- **Background**: Gradient from gray-900 via purple-900 to indigo-900
- **Logo**: 💎 crystal emoji (text size: 2xl/24px)
- **Brand Name**: "Világműhely" (font size: xl/20px, bold, white text)
- **Layout**: Centered logo + text, padding 20px top/bottom
- **Link**: Clickable area to homepage URL
- **Border**: Bottom border with white/10% opacity for separation

### Footer Design Specifications
- **Background**: Dark with subtle gradient (black/20% opacity overlay)
- **Content Sections**:
  - Company info with crystal logo
  - Links grid (4 columns on desktop, stacked on mobile):
    - Explore: Crystal Gallery, Dashboard
    - Resources: Documentation, Help Center, Community
    - Connect: GitHub, Discord, Twitter
  - Copyright: "© {year} Világműhely. Operated by Webtech Solutions"
- **Typography**:
  - Section headings: font-bold, 14px
  - Links: 12px, gray-400 color
  - Hover: white text transition
- **Spacing**: Consistent padding (32px), gap between sections (8px)
- **Border**: Top border with white/10% opacity

### CSS Theme Updates
- Update wrapper background to gradient
- Update header styles for new branding
- Add gradient backgrounds
- Update footer styles to match design
- Ensure mobile responsive (max-width: 600px)
- Add hover effects for links
- Update color palette:
  - Primary: #9333ea (purple-600)
  - Secondary: #0891b2 (cyan-600)
  - Accent: #ec4899 (pink-600)
  - Dark background: #1f2937 (gray-900)
  - Light text: #ffffff
  - Muted text: #9ca3af (gray-400)

## Email Client Compatibility
- Use table-based layout for maximum compatibility
- Inline CSS styles where needed
- Test in major email clients:
  - Gmail (web, mobile)
  - Outlook (2016+)
  - Apple Mail
  - Yahoo Mail
  - Mobile clients (iOS Mail, Android Gmail)

## UX/UI Best Practices
1. **Brand Consistency**: Match homepage visual identity
2. **Readability**: High contrast text, adequate spacing
3. **Mobile First**: Responsive design for all screen sizes
4. **Performance**: Optimize gradient rendering, use system fonts
5. **Accessibility**: Proper alt text, semantic HTML, ARIA labels
6. **Click Targets**: Large enough touch targets (min 44x44px)
7. **Visual Hierarchy**: Clear separation between header, content, footer

## Implementation Steps
1. Update header component with gradient background and crystal logo
2. Update footer component with multi-column layout and links
3. Modify CSS theme with new color palette and styles
4. Test across email clients
5. Validate responsive behavior
6. Check accessibility compliance

## Testing Checklist
- [ ] Visual match with homepage design
- [ ] Header displays crystal logo + brand name
- [ ] Header links to homepage
- [ ] Footer has 4-column layout (desktop)
- [ ] Footer stacks properly (mobile)
- [ ] All links functional
- [ ] Gradient backgrounds render correctly
- [ ] Text readable with high contrast
- [ ] Mobile responsive at 320px, 375px, 600px widths
- [ ] Works in Gmail, Outlook, Apple Mail
- [ ] Hover effects work on desktop
- [ ] Copyright year dynamic

## Success Criteria
- Email templates visually consistent with homepage
- Professional, modern appearance
- All email clients display properly (may degrade gracefully)
- Mobile-friendly responsive design
- Improved brand recognition
- Maintained email deliverability (no spam filter triggers)

## Notes
- Email CSS has limitations - complex gradients may simplify in some clients
- Use fallback colors for gradients
- Inline critical styles for email client compatibility
- Keep file sizes reasonable for email performance
