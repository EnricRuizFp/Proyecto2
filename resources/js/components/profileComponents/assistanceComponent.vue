<template>
    <div class="assistance-container">
        <h3 class="assistance-title h4 white-color">{{ $t("Assistance_center") }}</h3>
        
        <div class="assistance-content">
            <p class="assistance-text">
                {{ $t("assistance_center_text") }}
            </p>

            <div class="contact-section">
                <h4 class="contact-title">{{ $t("direct_contact") }}</h4>
                <div class="email-list">
                    <div class="email-item">
                        <span class="email-label">{{ $t("general_support") }}</span>
                        <span class="email">enricruizfp@ibf.cat</span>
                    </div>
                    <div class="email-item">
                        <span class="email-label">{{ $t("technical_support") }}</span>
                        <span class="email">danielloberafp@ibf.cat</span>
                    </div>
                </div>

                <a href="mailto:erb@erb.com,dls@dls.com" class="contact-button">
                    {{ $t("contact_via_email") }}
                </a>
            </div>
        </div>
    </div>
</template>