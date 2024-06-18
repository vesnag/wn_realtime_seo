
Custom Implementation for Real-time SEO - Extension for Drupal 8
----------------

**Note:**
This is a temporary public repository for a take-home challenge. It will be marked as private once the review is complete.

### Overview
Real-time SEO for Entities is an extension of the Yoast SEO module for Drupal. While the Yoast SEO module can only analyse the body field, this module extends its functionality to analyse multiple fields across various entities.

### How It Works
This module functions as an extension of the Yoast SEO module, allowing additional fields beyond the body field to be analysed for SEO. It uses the same principles and analysis techniques but applies them to a broader range of fields and entities.

For detailed information, refer to the Real-time SEO for Drupal documentation.

### Configuration
To configure this module:
- Ensure that the Yoast SEO module is installed and enabled.
- Enable this custom Real-time SEO module.
- Navigate to Configuration > Development > Real-time SEO Admin Settings and ensure that the content types to be analysed are set accordingly.
- Go to Structure > Content types > Your content type > Manage form display.
- Use the "Real-time SEO Form - Multiple fields" widget introduced by this module.
- Set the fields you want to be analysed as body and summary fields.

For additional configuration details, refer to the Real-time SEO for Drupal documentation.

### Features that can be implemented
This module is a proof of concept designed to enhance the capabilities of the original "Real-time SEO for Drupal" module, maintaining the same behavior while expanding its analytical scope.

Features that can be implemented
- Allows analysis of multiple fields simultaneously.
- Retrieves and analyse fields from any entity, not just paragraphs.
- Supports systems with multiple entity relationships. Currently, it is designed to handle single target bundles, with logic adaptable for multiple bundles if needed.
