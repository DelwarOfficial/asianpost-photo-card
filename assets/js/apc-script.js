(function ($) {
    'use strict';

    class AsianPostPhotoCard {
        constructor() {
            this.currentScale = 1;
            this.isDragging = false;
            this.dragStartX = 0;
            this.dragStartY = 0;
            this.initialLeft = 0;
            this.initialTop = 0;

            this.initElements();
            this.initEvents();

            // Force load the selected template
            this.$templateSelect.trigger('change');

            setTimeout(() => this.resizePreview(), 200);
            // Backup resize for lazy loading
            setTimeout(() => this.resizePreview(), 1000);
        }

        initElements() {
            this.$urlInput = $('#apc-url');
            this.$customImageInput = $('#apc-custom-image-input'); // New
            this.$imageScaleInput = $('#apc-image-scale'); // New Zoom Slider

            this.$templateSelect = $('#apc-template');
            this.$fontSelect = $('#apc-font-family'); // New Font Selector
            this.$qrToggle = $('#apc-toggle-qr'); // New QR Toggle
            this.$generateBtn = $('#apc-generate-btn');
            this.$copyBtn = $('#apc-copy');
            this.$downloadBtn = $('#apc-download');
            this.$resetBtn = $('#apc-reset-btn');
            this.$message = $('#apc-message');
            this.$spinner = $('.apc-spinner');

            // Custom Title & Font Size
            this.$titleInput = $('#apc-custom-title');
            this.$highlightToggle = $('#apc-toggle-highlight'); // New Highlight Toggle
            this.$fontSizeInput = $('#apc-font-size');
            this.$fontSizeRange = $('#apc-font-size-range');

            this.$previewContainer = $('.apc-preview-container-box');
            this.$wrapper = $('.apc-preview-wrapper');
            this.$card = $('#apc-card');
            this.$bg = $('#apc-bg');
            this.$photo = $('#apc-photo');
            this.$dateText = $('.apc-date-text');
            this.$titleArea = $('.apc-title-area');

            this.$containers = $('.apc-date-pill, .apc-photo-container');
            this.$photoContainer = $('.apc-photo-container'); // Need this reference
            this.$qr = $('.apc-qr');

            this.$zoomValText = $('#apc-zoom-val');
            this.$fontSizeValText = $('#apc-font-size-val');
            this.$fileNameText = $('#apc-file-name');
            this.$fadeTarget = $('#apc-card');
            this.$sections = $('.apc-section-title');
            this.currentData = null;
        }

        initEvents() {
            // Accordion Toggle
            this.$sections.on('click', function () {
                $(this).closest('.apc-section').toggleClass('is-closed');
            });

            // Require URL for Generate
            this.$urlInput.on('input', (e) => {
                const isValid = $(e.target).val().trim() !== '';
                this.$generateBtn.prop('disabled', !isValid);
            });

            this.$generateBtn.on('click', (e) => this.handleGenerate(e));
            this.$copyBtn.on('click', (e) => this.copyCardToClipboard(e));
            this.$downloadBtn.on('click', (e) => this.downloadCard(e));
            this.$resetBtn.on('click', () => this.resetForm());

            this.$templateSelect.on('change', (e) => {
                const val = $(e.target).val();
                if (val) {
                    const src = apcData.templatesUrl + val;
                    this.$bg.attr('src', src);
                }
            });

            // FONT DROPDOWN EVENT
            this.$fontSelect.on('change', () => {
                this.renderStyledTitle(this.$titleInput.val());
            });

            // CUSTOM IMAGE UPLOAD
            this.$customImageInput.on('change', (e) => this.handleCustomImage(e));

            // CUSTOM IMAGE ZOOM
            this.$imageScaleInput.on('input', (e) => this.updateImageTransform());

            // LIVE TITLE EDITING EVENTS
            this.$titleInput.on('input', (e) => this.renderStyledTitle($(e.target).val()));

            // HIGHLIGHT TOGGLE
            this.$highlightToggle.on('change', () => {
                this.renderStyledTitle(this.$titleInput.val());
            });

            // FONT SIZE - DUAL BINDING
            this.$fontSizeInput.on('input', () => {
                const val = this.$fontSizeInput.val();
                this.$fontSizeRange.val(val);
                if (this.$fontSizeValText.length) this.$fontSizeValText.text(val + 'px');
                this.renderStyledTitle(this.$titleInput.val());
            });

            this.$fontSizeRange.on('input', () => {
                const val = this.$fontSizeRange.val();
                this.$fontSizeInput.val(val);
                if (this.$fontSizeValText.length) this.$fontSizeValText.text(val + 'px');
                this.renderStyledTitle(this.$titleInput.val());
            });

            $(window).on('resize', () => this.resizePreview());

            // QR Toggle
            this.$qrToggle.on('change', () => {
                if (this.currentData) {
                    this.renderQR(this.currentData);
                } else if (this.$urlInput.val().trim()) {
                    this.renderQR({ qr_url: this.$urlInput.val().trim() });
                }
            });

            // DRAG & DROP INIT
            this.initDraggable();
        }

        initDraggable() {
            // Title Drag
            this.$titleArea.addClass('apc-draggable');
            this.$titleArea.on('mousedown touchstart', (e) => this.startDrag(e, 'title'));

            // Photo Drag
            // Photo defaults to 'grab' cursor via CSS
            this.$photo.on('mousedown touchstart', (e) => this.startDrag(e, 'photo'));

            // QR Drag
            this.$qr.addClass('apc-draggable');
            this.$qr.on('mousedown touchstart', (e) => this.startDrag(e, 'qr'));

            // Global Move/End
            $(window).on('mousemove touchmove', (e) => this.onDrag(e));
            $(window).on('mouseup touchend', () => this.stopDrag());
        }

        startDrag(e, type) {
            if ($(e.target).is('input, textarea')) return;
            // Prevent default drag for images causing browser copy ghost
            e.preventDefault();

            let clientX, clientY;
            if (e.type === 'touchstart') {
                clientX = e.originalEvent.touches[0].clientX;
                clientY = e.originalEvent.touches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }

            this.isDragging = true;
            this.dragTarget = type; // 'title' or 'photo'
            this.dragStartX = clientX;
            this.dragStartY = clientY;

            // Cache Initial Positions
            if (type === 'title') {
                let curLeft = parseFloat(this.$titleArea.css('left'));
                let curTop = parseFloat(this.$titleArea.css('top'));
                this.initialLeft = isNaN(curLeft) ? 20 : curLeft;
                this.initialTop = isNaN(curTop) ? 950 : curTop;
                this.$titleArea.css('cursor', 'grabbing');
            } else if (type === 'photo') {
                let curLeft = parseFloat(this.$photo.css('left'));
                let curTop = parseFloat(this.$photo.css('top'));
                this.initialLeft = isNaN(curLeft) ? 22.2222 : curLeft;
                this.initialTop = isNaN(curTop) ? 276.717 : curTop;
                this.$photo.css('cursor', 'grabbing');
            } else if (type === 'qr') {
                const pos = this.$qr.position();
                this.initialLeft = pos.left;
                this.initialTop = pos.top;
                this.$qr.css('cursor', 'grabbing');
            }
        }

        onDrag(e) {
            if (!this.isDragging) return;

            let clientX, clientY;
            if (e.type === 'touchmove') {
                e.preventDefault(); // Prevent scroll
                clientX = e.originalEvent.touches[0].clientX;
                clientY = e.originalEvent.touches[0].clientY;
            } else {
                // e.preventDefault();
                clientX = e.clientX;
                clientY = e.clientY;
            }

            // Adjust by Preview Scale
            const deltaX = (clientX - this.dragStartX) / this.currentScale;
            const deltaY = (clientY - this.dragStartY) / this.currentScale;

            const newLeft = this.initialLeft + deltaX;
            const newTop = this.initialTop + deltaY;

            if (this.dragTarget === 'title') {
                this.$titleArea.css({ left: newLeft + 'px', top: newTop + 'px' });
            } else if (this.dragTarget === 'photo') {
                this.$photo.css({ left: newLeft + 'px', top: newTop + 'px' });
            } else if (this.dragTarget === 'qr') {
                this.$qr.css({ left: newLeft + 'px', top: newTop + 'px', inset: 'auto', right: 'auto', bottom: 'auto' });
            }
        }

        stopDrag() {
            if (this.isDragging) {
                this.isDragging = false;
                if (this.dragTarget === 'title') this.$titleArea.css('cursor', 'grab');
                if (this.dragTarget === 'photo') this.$photo.css('cursor', 'grab');
                if (this.dragTarget === 'qr') this.$qr.css('cursor', 'grab');
                this.dragTarget = null;
            }
        }

        updateImageTransform() {
            const scale = this.$imageScaleInput.val();
            if (this.$zoomValText.length) this.$zoomValText.text(Number(scale).toFixed(1) + 'x');
            // We use standard CSS transform for zoom
            // Note: If we had a rotation logic, we'd append it here.
            this.$photo.css('transform', `scale(${scale})`);
        }

        handleCustomImage(e) {
            const file = e.target.files[0];
            if (!file) {
                if (this.$fileNameText.length) this.$fileNameText.text('Upload Image');
                return;
            }
            if (this.$fileNameText.length) this.$fileNameText.text(file.name);

            // XSS Protection: Basic file type check
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                this.showMessage('Invalid file type. Please use JPG, PNG, GIF, or WEBP.', 'error');
                return;
            }

            // Memory Management: Revoke previous URL to prevent unauthorized storage/leaks
            if (this.currentCustomImage) {
                URL.revokeObjectURL(this.currentCustomImage);
            }

            // Zero-Storage: Create a temporary Blob URL. This exists only in browser memory.
            this.currentCustomImage = URL.createObjectURL(file);
            this.setNewImage(this.currentCustomImage);

            this.showMessage('Image loaded locally.', 'success');
        }

        setNewImage(src) {
            this.$photo.attr('src', src);
            this.$containers.fadeIn();

            // Reset Image Position & Zoom on new image load
            this.$photo.css({
                top: '276.717px',
                left: '22.2222px',
                transform: 'scale(1)'
            });
            this.$imageScaleInput.val(1);
        }

        handleGenerate(e) {
            e.preventDefault();
            const url = this.$urlInput.val().trim();

            // Basic XSS Sanitize (though jQuery .val() is generally safe from DOM injection, we validat logic)
            if (!url) {
                this.showMessage(apcData.strings.invalidUrl, 'error');
                return;
            }

            this.toggleLoading(true);
            this.$message.hide();

            $.post(apcData.ajaxurl, {
                action: 'APC_get_external_post',
                nonce: apcData.nonce,
                post_url: url
            }, (response) => {
                this.toggleLoading(false);

                if (response.success) {
                    this.currentData = response.data;

                    // Securely handle Headline (prevent Reflected XSS)
                    let safeTitle = '';
                    if (this.currentData.title) {
                        // Create a temp div to encode HTML entities
                        safeTitle = $('<div>').text(this.currentData.title).html();
                        this.$titleInput.val(safeTitle);
                    }

                    this.renderCard(response.data);

                    this.$copyBtn.show();
                    this.$downloadBtn.show();

                    this.showMessage('Card Generated Successfully!', 'success');

                    // Force Resize after image load
                    setTimeout(() => this.resizePreview(), 100);

                } else {
                    this.showMessage(response.data.message || 'Error fetching data', 'error');
                }
            }).fail(() => {
                this.toggleLoading(false);
                this.showMessage('Server connection failed', 'error');
            });
        }

        renderCard(data) {
            this.$containers.fadeIn();

            // Trigger CSS fade reflow for Card
            if (this.$fadeTarget.length) {
                this.$fadeTarget.removeClass('fade-in');
                void this.$fadeTarget[0].offsetWidth; // trigger reflow
                this.$fadeTarget.addClass('fade-in');
            }

            this.$card.removeClass('apc-font-bn apc-font-en');
            const langClass = (data.language === 'bangla') ? 'apc-font-bn' : 'apc-font-en';
            this.$card.addClass(langClass);

            // Auto-update the font dropdown to match the detected article language
            if (data.language === 'bangla') {
                this.$fontSelect.val('TiroBangla-Regular.woff2');
            } else {
                this.$fontSelect.val('GandhiSerif-Bold.woff2');
            }

            this.$dateText.text(data.date);

            // Only update image if user hasn't uploaded a custom one OR if they want to overwrite
            // Current flow: 'Generate' fetches article data. Usually overwrites custom logic.
            // If user wants custom image, they likely upload it AFTER generation or want it to persist?
            // "Add a file input... to allow users to upload/choose a custom background image"
            // If I upload image, then click Generate, should it be overwritten? 
            // Usually Generate fetches new article -> new image.

            const imgSrc = data.image ? data.image : apcData.defaultImage;
            // If we want to check if custom image is set... complicated without flags.
            // Simple behavior: Generate overwrites. User re-uploads if needed.

            // Reuse logic to set image and reset pos
            this.setNewImage(imgSrc);

            // Calculate optimal font size logic
            // We set the input, and then renderTitle uses the input value
            const optimalSize = this.calculateOptimalSize(data.title || '');
            this.$fontSizeInput.val(optimalSize);
            this.$fontSizeRange.val(optimalSize); // Sync slider

            this.renderStyledTitle(data.title);

            // Render QR using shortlink data
            this.renderQR(data);
        }

        renderQR(data) {
            const url = data && data.qr_url ? data.qr_url : this.$urlInput.val().trim();

            // Clear previous QR
            this.$qr.empty();

            if (!url || !this.$qrToggle.is(':checked')) {
                this.$qr.hide();
                return;
            }

            if (typeof QRCode === 'undefined') {
                console.warn('QRCode library not loaded');
                this.$qr.hide();
                return;
            }

            try {
                // Generate QR code client-side
                new QRCode(this.$qr[0], {
                    text: url,
                    width: 130, // Matches CSS
                    height: 130,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });

                this.$qr.show();

                // Fix for QR image display
                setTimeout(() => {
                    this.$qr.find('img').css({ 'width': '100%', 'height': '100%', 'display': 'block' });
                }, 100);

            } catch (error) {
                console.error('QR Code generation error:', error);
                this.$qr.hide();
            }
        }

        calculateOptimalSize(title) {
            if (!title) return 75;
            const words = title.split(/\s+/).length;
            if (words > 20) return 44;
            if (words > 15) return 52;
            if (words > 10) return 60;
            return 75;
        }

        renderStyledTitle(title) {
            if (!title) {
                this.$titleArea.empty();
                return;
            }

            title = title.replace(/\|.*$/, '').trim();

            // SECURITY: Escape HTML entities to prevent XSS
            title = title.replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            let html = '';

            // --- HIGHLIGHT TOGGLE CHECK ---
            if (!this.$highlightToggle.is(':checked')) {
                // If disabled, just render plain text with line breaks (literal asterisks)
                html = title.replace(/\n/g, '<br/>');
            } else {
                // --- 1. ASTERISK MODE (*highlight*) ---
                if (title.includes('*')) {
                    // Handle newlines
                    let formatted = title.replace(/\n/g, '<br/>');
                    // Regex to replace *text* with <span class="apc-highlight">text</span>
                    // Uses non-greedy match (.*?)
                    html = formatted.replace(/\*(.*?)\*/g, '<span class="apc-highlight">$1</span>');

                } else {
                    // --- 2. AUTO MODE (Fallback) ---
                    // Clean and split
                    let formattedTitle = title.replace(/\n/g, " ###BR### ");
                const tokens = formattedTitle.split(/\s+/);

                const realWords = tokens.filter(t => t !== '###BR###' && t.trim() !== '');
                const total = realWords.length;
                const start = Math.floor(total * 0.30);
                const end = Math.floor(total * 0.70);

                let wordIndex = 0;

                tokens.forEach((token) => {
                    if (token === '###BR###') {
                        html += '<br/>';
                    } else if (token.trim() !== '') {
                        if (total > 3 && wordIndex >= start && wordIndex <= end) {
                            html += `<span class="apc-highlight">${token}</span> `;
                        } else {
                            html += `${token} `;
                        }
                        wordIndex++;
                    }
                });
            }
        }

        // Get size from input
            let size = this.$fontSizeInput.val();
            if (!size) size = 75;

            // Get selected font
            let selectedFontFile = this.$fontSelect.val() || 'TiroBangla-Regular.woff2';
            let fontName = selectedFontFile.split('.')[0];

            // Dynamically load font to support future extensibility
            if ($(`#apc-dynamic-font-${fontName}`).length === 0) {
                let fontUrl = apcData.fontsUrl + selectedFontFile;
                let css = `@font-face { font-family: '${fontName}'; src: url('${fontUrl}') format('woff2'); font-style: normal; font-display: swap; }`;
                $('head').append(`<style id="apc-dynamic-font-${fontName}">${css}</style>`);
            }

            let fontFamily = `'${fontName}', sans-serif`;

            // Apply font family globally to areas requiring text styling
            this.$dateText.css('font-family', fontFamily);
            this.$card.find('.apc-date-pill').css('font-family', fontFamily);

            this.$titleArea.html(
                `<div class="apc-title-line" style="font-size:${size}px; line-height: 1.3; display:block; font-family:${fontFamily}">${html}</div>`
            );
        }

        copyCardToClipboard(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const originalText = $btn.text();

            if (!navigator.clipboard || !window.ClipboardItem) {
                alert('Copy requires HTTPS.');
                return;
            }

            $btn.prop('disabled', true).text(apcData.strings.copying);

            this.createTempCanvas((canvas, $container) => {
                canvas.toBlob(blob => {
                    if (!blob) return this.handleError($btn, originalText, $container);
                    try {
                        const item = new ClipboardItem({ "image/png": blob });
                        navigator.clipboard.write([item]).then(() => {
                            this.showMessage(apcData.strings.copySuccess, 'success');
                            $btn.prop('disabled', false).text(originalText);
                            $container.remove();
                        }).catch(() => this.handleError($btn, originalText, $container));
                    } catch (err) {
                        this.handleError($btn, originalText, $container);
                    }
                }, 'image/png');
            }, () => this.handleError($btn, originalText));
        }

        downloadCard(e) {
            e.preventDefault();
            const $btn = $(e.currentTarget);
            const originalText = $btn.text();

            $btn.prop('disabled', true).text('Processing...');

            this.createTempCanvas((canvas, $container) => {
                const link = document.createElement('a');
                link.download = `asian-post-card-${Date.now()}.png`;
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                $container.remove();
                $btn.prop('disabled', false).text(originalText);
                this.showMessage('Image downloaded!', 'success');
            }, () => this.handleError($btn, originalText));
        }

        handleError($btn, originalText, $container) {
            this.showMessage(apcData.strings.copyError, 'error');
            $btn.prop('disabled', false).text(originalText);
            if ($container) $container.remove();
        }

        createTempCanvas(successCallback, errorCallback) {
            // Create hidden clone
            const $captureContainer = $('<div>').attr('id', 'apc-capture-container').css({
                position: 'fixed',
                left: '-9999px', // Safe off-screen
                top: '0',
                width: '1080px',
                height: '1350px',
                zIndex: -9999,
                overflow: 'hidden'
            }).appendTo('body');

            const $clonedCard = this.$card.clone();

            // Fix QR in clone
            const originalQRImg = this.$qr.find('img');
            const cloneQRContainer = $clonedCard.find('.apc-qr');
            if (originalQRImg.length > 0) {
                cloneQRContainer.show().html(`<img src="${originalQRImg.attr('src')}" style="width:100%; height:100%; display:block;">`);
            }

            $clonedCard.css({
                'transform': 'none',
                'margin': '0',
                'width': '1080px',
                'height': '1350px',
                'position': 'relative'
            });

            $captureContainer.append($clonedCard);

            // Wait for images
            const images = $clonedCard.find('img');
            let loaded = 0;
            const total = images.length;

            const captureNow = () => {
                setTimeout(() => {
                    /**
                     * CRITICAL FIX: html2canvas does not correctly rasterize object-fit: cover combined with CSS transforms.
                     * We manually compute the "covered" geometry including user zoom/drag, and apply explicit 
                     * pixel dimensions to the cloned image. This ensures WYSIWYG export at 1080x1350.
                     */
                    const $clonedPhoto = $clonedCard.find('#apc-photo');
                    const $clonedContainer = $clonedCard.find('.apc-photo-container');

                    if ($clonedPhoto.length && $clonedContainer.length) {
                        const img = $clonedPhoto[0];
                        const cw = $clonedPhoto.width() || 1080;
                        const ch = $clonedPhoto.height() || 607.5;
                        const iw = img.naturalWidth;
                        const ih = img.naturalHeight;

                        if (iw && ih) {
                            // 1. Get User Controls (Scale & Drag Offsets)
                            const userScale = parseFloat(this.$imageScaleInput.val()) || 1;
                            const userLeft = parseFloat($clonedPhoto.css('left')) || 0;
                            const userTop = parseFloat($clonedPhoto.css('top')) || 0;

                            // 2. Compute "Cover" Dimensions (Math.max of ratios)
                            const scaleFactor = Math.max(cw / iw, ch / ih);
                            const drawnWidth = iw * scaleFactor;
                            const drawnHeight = ih * scaleFactor;

                            // 3. Apply User Zoom
                            const finalWidth = drawnWidth * userScale;
                            const finalHeight = drawnHeight * userScale;

                            // 4. Compute Position (Center Base + properties)
                            // (cw - finalWidth)/2 centers the scaled image
                            // + userLeft adds the drag delta
                            const finalLeft = (cw - finalWidth) / 2 + userLeft;
                            const finalTop = (ch - finalHeight) / 2 + userTop;

                            // 5. Apply Normalized Styles to CLONE only
                            $clonedPhoto.css({
                                'width': finalWidth + 'px',
                                'height': finalHeight + 'px',
                                'left': finalLeft + 'px',
                                'top': finalTop + 'px',
                                'transform': 'none',
                                'object-fit': 'fill', // Force exact fill of computed bounds
                                'max-width': 'none',
                                'max-height': 'none'
                            });
                        }
                    }

                    html2canvas($clonedCard[0], {
                        scale: 1,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        allowTaint: false, // SECURITY: Prevent taint to ensure clean export and avoid cross-origin leaks
                        width: 1080,
                        height: 1350,
                        windowWidth: 1080,
                        windowHeight: 1350,
                        x: 0,
                        y: 0,
                        scrollY: 0,
                        scrollX: 0
                    }).then(canvas => {
                        // Double-check output dimensions
                        if (canvas.width !== 1080 || canvas.height !== 1350) {
                            const fixedCanvas = document.createElement('canvas');
                            fixedCanvas.width = 1080;
                            fixedCanvas.height = 1350;
                            const ctx = fixedCanvas.getContext('2d');
                            ctx.drawImage(canvas, 0, 0, 1080, 1350);
                            successCallback(fixedCanvas, $captureContainer);
                        } else {
                            successCallback(canvas, $captureContainer);
                        }
                    }).catch(err => {
                        console.error(err);
                        $captureContainer.remove();
                        if (errorCallback) errorCallback();
                    });
                }, 200);
            };

            if (total === 0) {
                captureNow();
            } else {
                images.each(function () {
                    if (this.complete) {
                        loaded++;
                        if (loaded >= total) captureNow();
                    } else {
                        $(this).on('load error', () => {
                            loaded++;
                            if (loaded >= total) captureNow();
                        });
                    }
                });
            }
        }

        resizePreview() {
            const containerWidth = this.$previewContainer.width();
            if (!containerWidth) return;

            // Scale logic
            let scale = containerWidth / 1080;
            // Prevent upscaling beyond 1.0 (or a reasonable max for desktop spacing)
            if (scale > 0.9) scale = 0.9;

            this.currentScale = scale; // Store scale for Drag Logic

            // Set wrapper size (physical space)
            this.$wrapper.css({
                'height': (1350 * scale) + 'px',
                'width': (1080 * scale) + 'px'
            });

            // Scale card
            this.$card.css({
                'transform': `scale(${scale})`,
                'transform-origin': 'top left' // Match wrapper size logic
            });
        }

        toggleLoading(isLoading) {
            if (isLoading) {
                this.$spinner.show();
                this.$generateBtn.prop('disabled', true);
            } else {
                this.$spinner.hide();
                this.$generateBtn.prop('disabled', false);
            }
        }

        showMessage(msg, type) {
            this.$message
                .removeClass('apc-success apc-error')
                .addClass('apc-' + type)
                .text(msg)
                .fadeIn(200);

            if (this.messageTimeout) clearTimeout(this.messageTimeout);
            this.messageTimeout = setTimeout(() => this.$message.fadeOut(300), 5000);
        }

        resetForm() {
            if (!confirm('Are you sure you want to reset all settings?')) return;

            this.$urlInput.val('');
            this.$titleInput.val('');
            this.$fontSizeInput.val(75); // Reset size
            this.$fontSizeRange.val(75);
            this.$fontSelect.val('TiroBangla-Regular.woff2'); // Reset font select
            this.$customImageInput.val(''); // Reset file input
            this.$qrToggle.prop('checked', false); // Reset toggle
            this.$highlightToggle.prop('checked', false); // Reset highlight toggle
            this.$containers.hide();
            this.$titleArea.empty();

            // Reset position
            this.$titleArea.css({ top: '950px', left: '20px' });
            this.$qr.css({ top: '', left: '', inset: '1186.58px auto auto 48.6886px' });

            this.$copyBtn.hide();
            this.$downloadBtn.hide();
            this.$message.hide();
            this.$photo.attr('src', '');
            this.$qr.hide().empty();
            this.currentData = null;
            this.resizePreview();
        }
    }

    $(document).ready(() => new AsianPostPhotoCard());

})(jQuery);