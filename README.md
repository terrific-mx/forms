# Terrific Forms

**Don’t build a backend just to handle forms.**

Terrific Forms provides form endpoints so you can use them on simple sites—like static sites—and keep your stack simple while handling forms.

## Why Terrific Forms?
- **No backend required:** Instantly get a POST endpoint for your HTML forms.
- **Works anywhere:** Use with static sites, landing pages, or any site where you control the HTML.
- **No JavaScript widgets:** Keep total control over your markup and styling. No iframes, no WYSIWYG editors, no extra scripts.
- **Simple and private:** Just HTML and CSS. We don’t inject anything or track your users.
- **Email forwarding:** Configure your forms to forward submissions to any email address you choose.

## How it works
1. Create a form in Terrific Forms to get a unique POST endpoint.
2. Optionally, set up email forwarding for your form submissions.
3. Use that endpoint in your HTML form’s `action` attribute.
4. Submissions are captured and managed for you—no backend code needed.

## Example
```html
<form action="https://terrificforms.com/forms/your-form-endpoint" method="POST">
  <input type="text" name="name" placeholder="Your Name" required />
  <input type="email" name="email" placeholder="Your Email" required />
  <button type="submit">Send</button>
</form>
```

---

Start capturing form submissions the easy way. Keep your stack simple with Terrific Forms.
