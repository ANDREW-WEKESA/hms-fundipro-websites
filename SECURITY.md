# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please email andrewwekesa675@gmail.com with:

1. Description of the vulnerability
2. Steps to reproduce
3. Potential impact
4. Suggested fix (if any)

**Please do not create public GitHub issues for security vulnerabilities.**

## Security Measures

### Current Implementation

- HTTPS enforcement via .htaccess
- Form validation (client and server-side)
- Honeypot spam protection
- Rate limiting on forms
- Input sanitization
- XSS protection
- CSRF tokens (planned)

### Data Protection

- Kenya Data Protection Act 2019 compliance
- Privacy policy and terms of service
- User consent for data collection
- Secure data transmission (HTTPS)
- No sensitive data stored in cookies

### Best Practices

- Regular security updates
- Code reviews before deployment
- Dependency vulnerability scanning
- Access control and authentication
- Secure password policies
- Regular backups

## Compliance

- **Kenya Data Protection Act 2019**: Personal data handling
- **WCAG 2.1 AA**: Accessibility standards
- **HTTPS**: Encrypted connections
- **GDPR-aligned**: Data subject rights

## Future Security Enhancements

- [ ] Implement CSRF protection
- [ ] Add Content Security Policy headers
- [ ] Enable HSTS
- [ ] Implement rate limiting
- [ ] Add SQL injection protection
- [ ] Set up WAF (Web Application Firewall)
- [ ] Regular penetration testing
- [ ] Security audit logging

## Contact

Security concerns: andrewwekesa675@gmail.com
