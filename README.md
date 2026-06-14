# Brighter Websites - Service Pathway Quizzes

A reusable WordPress plugin for creating diagnostic quizzes that route users to service pathways.

## Installation

1. **Upload Plugin**
   - Copy the `brighter-websites-quizzes` folder to `/wp-content/plugins/`
   - Activate from WordPress Admin → Plugins

2. **Verify Assets Load**
   - CSS only loads when shortcode is present (performance optimized)
   - JS only loads when shortcode is present

## Usage

### Basic Shortcode

Add to any page or post:

```
[bw_service_pathway_quiz]
```

With custom titles:

```
[bw_service_pathway_quiz title="Find Your Pathway" subtitle="Quick 5-question diagnosis"]
```

## Configuration

### Google Sheets Integration (Zapier/Make)

1. **Get Webhook URL**
   - Set up a Zap in Zapier or Automation in Make
   - Trigger: Webhook (catch raw data)
   - Action: Google Sheets → Append row
   - Copy the webhook URL

2. **Add to WordPress**
   - Go to WordPress Admin → Settings → Brighter Quizzes
   - Paste webhook URL into "Webhook Endpoint" field
   - Save

3. **Webhook Payload Format**
   ```json
   {
     "timestamp": "2026-01-09T10:30:00+00:00",
     "quiz_id": "service-pathway",
     "name": "John Doe",
     "email": "john@example.com",
     "pathway": "growth_cro",
     "diagnosis": "You have traffic; conversions are the constraint...",
     "answers": {
       "q1": "stage_2_sell_stuff",
       "q2": "bottleneck_conversions",
       "q3": "urgency_budget_refine",
       "q4": "fear_conversion",
       "q5": "endstate_optimize"
     }
   }
   ```

### Email Confirmation

Emails are sent automatically to users who provide their email. Customize in `class-webhook-handler.php`:

```php
self::send_confirmation_email( $email, $name, $result );
```

## Customization

### Adding New Questions

Edit `includes/class-shortcode-handler.php` → `render_service_pathway_quiz()`:

```html
<!-- Question 6: New Question -->
<div class="bw-quiz-question" data-question="q6" style="display: none;">
    <h3 class="bw-question-title">Your question here?</h3>
    <div class="bw-question-options">
        <label class="bw-option">
            <input type="radio" name="q6" value="new_answer_1" required>
            <span class="bw-option-text">Option 1</span>
        </label>
        <!-- More options -->
    </div>
</div>
```

Then update routing logic in `class-quiz-engine.php` → `determine_pathway()`.

### Modifying Routing Logic

Edit `class-quiz-engine.php` → `determine_pathway()`:

```php
// Add your custom routing logic
if ( $q1 === 'stage_2_sell_stuff' && $q2 === 'bottleneck_visibility' ) {
    return 'launch';
}
```

### Customizing Diagnosis Messages

Edit `class-quiz-engine.php` → `get_diagnosis()`:

```php
$diagnoses = array(
    'launch' => 'Your custom message here',
    // ...
);
```

### Customizing Styling

Use your theme's CSS variables in `assets/css/quiz-frontend.css`:

- `--bde-brand-primary-color`: Brand color
- `--bde-headings-color`: Heading color
- `--bde-body-text-color`: Body text
- `--bde-heading-font-family`: Font for headings
- `--bde-body-font-family`: Font for body text

All colors and fonts will match your theme automatically.

## Performance Notes

- **Lazy Loading**: CSS/JS only load when `[bw_service_pathway_quiz]` shortcode is present
- **Async Webhooks**: Quiz submissions send to webhooks asynchronously (non-blocking)
- **No Database Writes**: Responses stored only via webhook/email, not in WordPress database
- **Minification**: Ready for production minification (JS/CSS)

## Troubleshooting

### Shortcode not displaying

- Check that plugin is activated
- Verify shortcode syntax: `[bw_service_pathway_quiz]`
- Check browser console for JS errors

### Webhook not receiving data

- Verify webhook URL is correct in WordPress settings
- Check that AJAX endpoint is accessible: `/wp-admin/admin-ajax.php`
- Verify nonce is valid

### Styling issues

- Ensure theme CSS variables are defined
- Check for CSS conflicts with other plugins
- Use browser inspector to verify variable values

## File Structure

```
brighter-websites-quizzes/
├── brighter-quizzes.php           # Main plugin file
├── includes/
│   ├── class-quiz-engine.php      # Core quiz logic & routing
│   ├── class-shortcode-handler.php # Renders quiz HTML
│   ├── class-webhook-handler.php   # Handles submissions & webhooks
│   └── quizzes/
│       └── service-pathway-quiz.php # Quiz configuration
├── assets/
│   ├── css/
│   │   └── quiz-frontend.css      # Frontend styles
│   └── js/
│       └── quiz-frontend.js       # Frontend interactivity
└── README.md
```

## Future Enhancements

- Admin dashboard to view submissions
- Custom pathway configurations without code
- A/B testing different question wordings
- Integration with CRM systems (HubSpot, Pipedrive, etc.)
- Additional quiz templates (pricing calculator, etc.)

## Support

For issues or feature requests, contact Brighter Websites support.

## License

GPL-2.0+
