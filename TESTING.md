# Testing Guide

## Manual Testing Checklist

### Responsive Design
- [ ] Test on mobile (320px, 375px, 414px)
- [ ] Test on tablet (768px, 1024px)
- [ ] Test on desktop (1280px, 1920px)
- [ ] Check landscape orientation
- [ ] Verify hamburger menu on mobile
- [ ] Test all touch targets (44px minimum)

### Cross-Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

### Forms
- [ ] All required fields validate
- [ ] Email format validation
- [ ] Phone number format (+254)
- [ ] Error messages display correctly
- [ ] Success messages show
- [ ] Honeypot spam protection works
- [ ] Submit buttons disabled during submission

### Navigation
- [ ] All internal links work
- [ ] External links open in new tab
- [ ] Mobile menu toggles correctly
- [ ] Smooth scrolling works
- [ ] Active page highlighted

### Content
- [ ] No lorem ipsum or placeholder text
- [ ] All images have alt text
- [ ] Phone numbers are +254 format
- [ ] Emergency numbers are 999/112
- [ ] Currency is KES
- [ ] Kenyan locations used

### Performance
- [ ] Page load under 3 seconds (3G)
- [ ] Images optimized
- [ ] CSS/JS minified (production)
- [ ] No console errors
- [ ] No broken links

### Accessibility
- [ ] Keyboard navigation works
- [ ] Screen reader friendly
- [ ] Color contrast ratios pass
- [ ] ARIA labels present
- [ ] Focus indicators visible

### SEO
- [ ] Meta descriptions present
- [ ] Title tags unique
- [ ] Heading hierarchy correct
- [ ] Sitemap accessible
- [ ] Robots.txt configured
- [ ] Schema markup (future)

## Automated Testing (Future)

```bash
# Install dependencies
npm install --save-dev jest puppeteer

# Run tests
npm test
```

### Example Test
```javascript
describe('HMS Homepage', () => {
  test('loads successfully', async () => {
    await page.goto('http://localhost:8001');
    await expect(page.title()).resolves.toMatch('HMS Hospital');
  });
  
  test('emergency number is Kenyan', async () => {
    const content = await page.content();
    expect(content).toMatch(/999|112/);
    expect(content).not.toMatch(/911/);
  });
});
```

## Performance Testing

### Tools
- Google PageSpeed Insights
- GTmetrix
- WebPageTest
- Lighthouse

### Targets
- Performance score: >90
- First Contentful Paint: <2s
- Largest Contentful Paint: <2.5s
- Time to Interactive: <3.5s

## Security Testing

- [ ] HTTPS enforced
- [ ] No mixed content warnings
- [ ] XSS protection enabled
- [ ] CSRF tokens (when backend added)
- [ ] SQL injection protection
- [ ] Rate limiting works

## Regression Testing

Before each release:
1. Run full manual checklist
2. Check all forms
3. Test on real devices
4. Verify Kenyan localization
5. Check analytics tracking
6. Test backup restore

## Bug Reporting

Use GitHub Issues with template:

```
**Bug Description:**
Clear description of the issue

**Steps to Reproduce:**
1. Go to...
2. Click on...
3. See error

**Expected Behavior:**
What should happen

**Actual Behavior:**
What actually happens

**Environment:**
- Browser:
- Device:
- Screen size:
```
