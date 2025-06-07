# Terrific Forms

**Don’t build a backend just to handle forms.**

Terrific Forms provides form endpoints so you can use them on simple sites—like static sites—and keep your stack simple while handling forms.

## Why Terrific Forms?
- **No backend required:** Instantly get a POST endpoint for your HTML forms.
- **Works anywhere:** Use with static sites, landing pages, or any site where you control the HTML.
- **No JavaScript widgets:** Keep total control over your markup and styling. No iframes, no WYSIWYG editors, no extra scripts.
- **Simple and private:** Just HTML and CSS. We don’t inject anything or track your users.
- **Email forwarding:** Configure your forms to forward submissions to any email address you choose.

## Tech Stack
- **UI:** Built with [Flux UI PRO](https://fluxui.dev/)
- **Authentication:** Powered by [WorkOS](https://workos.com/) for secure, enterprise-ready authentication

## Setup Instructions

1. **Clone the repository:**
   ```sh
   git clone https://github.com/terrific-mx/terrific-forms.git
   cd terrific-forms
   ```
2. **Install dependencies:**
   ```sh
   composer install
   npm install
   ```
3. **Copy and configure environment file:**
   ```sh
   cp .env.example .env
   # Edit .env to set your database, mail, and WorkOS settings
   # (WorkOS settings are required for authentication)
   ```
4. **Generate application key:**
   ```sh
   php artisan key:generate
   ```
5. **Run migrations:**
   ```sh
   php artisan migrate
   ```
6. **Build frontend assets:**
   ```sh
   npm run build
   ```
7. **Start the development server:**
   ```sh
   php artisan serve
   ```

Now you can access Terrific Forms at `http://localhost:8000`.

## How it works
1. Create a form in Terrific Forms to get a unique POST endpoint.
2. Optionally, set up email forwarding for your form submissions.
3. Use that endpoint in your HTML form’s `action` attribute.
4. Submissions are captured and managed for you—no backend code needed.

## Example
```html
<form action="https://example.com/f/your-form-ulid" method="POST">
  <input type="text" name="name" placeholder="Your Name" required />
  <input type="email" name="email" placeholder="Your Email" required />
  <button type="submit">Send</button>
</form>
```

---

Start capturing form submissions the easy way. Keep your stack simple with Terrific Forms.
