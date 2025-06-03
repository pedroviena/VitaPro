/**
 * VitaPro Appointments FSE Block Editor JavaScript
 * 
 * Handles block editor functionality for the plugin.
 */

(function(blocks, element, blockEditor, components, i18n) {
    const { registerBlockType } = blocks;
    const { Fragment } = element;
    const { InspectorControls, useBlockProps } = blockEditor;
    const { PanelBody, SelectControl, ToggleControl, RangeControl, TextControl } = components;
    const { __ } = i18n;
    
    // Register Booking Form Block
    registerBlockType('vitapro-appointments-fse/booking-form', {
        title: __('Appointment Booking Form', 'vitapro-appointments-fse'),
        description: __('Display an appointment booking form.', 'vitapro-appointments-fse'),
        category: 'widgets',
        icon: 'calendar-alt',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            serviceId: {
                type: 'string',
                default: ''
            },
            professionalId: {
                type: 'string',
                default: ''
            },
            showServiceStep: {
                type: 'boolean',
                default: true
            },
            showProfessionalStep: {
                type: 'boolean',
                default: true
            },
            formId: {
                type: 'string',
                default: ''
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps({
                className: 'vitapro-booking-form-wrapper'
            });
            
            // Get services and professionals from the global variable
            const services = window.vitaproBlockEditor?.services || [];
            const professionals = window.vitaproBlockEditor?.professionals || [];
            
            // Convert services to options
            const serviceOptions = [
                { label: __('Select a service', 'vitapro-appointments-fse'), value: '' },
                ...services.map(service => ({
                    label: service.title,
                    value: service.id.toString()
                }))
            ];
            
            // Convert professionals to options
            const professionalOptions = [
                { label: __('Select a professional', 'vitapro-appointments-fse'), value: '' },
                ...professionals.map(professional => ({
                    label: professional.title,
                    value: professional.id.toString()
                }))
            ];
            
            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Booking Form Settings', 'vitapro-appointments-fse')}>
                            <SelectControl
                                label={__('Pre-select Service', 'vitapro-appointments-fse')}
                                value={attributes.serviceId}
                                options={serviceOptions}
                                onChange={(value) => setAttributes({ serviceId: value })}
                            />
                            
                            <SelectControl
                                label={__('Pre-select Professional', 'vitapro-appointments-fse')}
                                value={attributes.professionalId}
                                options={professionalOptions}
                                onChange={(value) => setAttributes({ professionalId: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Service Selection Step', 'vitapro-appointments-fse')}
                                checked={attributes.showServiceStep}
                                onChange={(value) => setAttributes({ showServiceStep: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Professional Selection Step', 'vitapro-appointments-fse')}
                                checked={attributes.showProfessionalStep}
                                onChange={(value) => setAttributes({ showProfessionalStep: value })}
                            />
                            
                            <TextControl
                                label={__('Form ID (optional)', 'vitapro-appointments-fse')}
                                value={attributes.formId}
                                onChange={(value) => setAttributes({ formId: value })}
                                help={__('Unique ID for this form. Useful when using multiple forms on the same page.', 'vitapro-appointments-fse')}
                            />
                        </PanelBody>
                    </InspectorControls>
                    
                    <div {...blockProps}>
                        <div className="vpa-block-preview">
                            <div className="vpa-block-preview-title">
                                {__('Appointment Booking Form', 'vitapro-appointments-fse')}
                            </div>
                            <div className="vpa-block-preview-description">
                                {__('This block displays an appointment booking form with the following settings:', 'vitapro-appointments-fse')}
                            </div>
                            <ul className="vpa-block-preview-settings">
                                <li>
                                    <strong>{__('Pre-selected Service:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.serviceId ? services.find(s => s.id.toString() === attributes.serviceId)?.title || attributes.serviceId : __('None', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Pre-selected Professional:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.professionalId ? professionals.find(p => p.id.toString() === attributes.professionalId)?.title || attributes.professionalId : __('None', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Service Selection:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showServiceStep ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Professional Selection:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showProfessionalStep ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                {attributes.formId && (
                                    <li>
                                        <strong>{__('Form ID:', 'vitapro-appointments-fse')}</strong>{' '}
                                        {attributes.formId}
                                    </li>
                                )}
                            </ul>
                        </div>
                    </div>
                </Fragment>
            );
        },
        
        save: function() {
            // Dynamic block, render on server
            return null;
        }
    });
    
    // Register Service List Block
    registerBlockType('vitapro-appointments-fse/service-list', {
        title: __('Service List', 'vitapro-appointments-fse'),
        description: __('Display a list of available services.', 'vitapro-appointments-fse'),
        category: 'widgets',
        icon: 'list-view',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            layout: {
                type: 'string',
                default: 'grid'
            },
            columns: {
                type: 'number',
                default: 3
            },
            showImage: {
                type: 'boolean',
                default: true
            },
            showDescription: {
                type: 'boolean',
                default: true
            },
            showPrice: {
                type: 'boolean',
                default: true
            },
            showDuration: {
                type: 'boolean',
                default: true
            },
            showBookButton: {
                type: 'boolean',
                default: true
            },
            categoryId: {
                type: 'string',
                default: ''
            },
            limit: {
                type: 'number',
                default: 0
            },
            bookingFormId: {
                type: 'string',
                default: ''
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps({
                className: 'vitapro-service-list-wrapper'
            });
            
            // Get categories from the global variable
            const categories = window.vitaproBlockEditor?.serviceCategories || [];
            
            // Convert categories to options
            const categoryOptions = [
                { label: __('All Categories', 'vitapro-appointments-fse'), value: '' },
                ...categories.map(category => ({
                    label: category.name,
                    value: category.id.toString()
                }))
            ];
            
            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Service List Settings', 'vitapro-appointments-fse')}>
                            <SelectControl
                                label={__('Layout', 'vitapro-appointments-fse')}
                                value={attributes.layout}
                                options={[
                                    { label: __('Grid', 'vitapro-appointments-fse'), value: 'grid' },
                                    { label: __('List', 'vitapro-appointments-fse'), value: 'list' }
                                ]}
                                onChange={(value) => setAttributes({ layout: value })}
                            />
                            
                            {attributes.layout === 'grid' && (
                                <RangeControl
                                    label={__('Columns', 'vitapro-appointments-fse')}
                                    value={attributes.columns}
                                    onChange={(value) => setAttributes({ columns: value })}
                                    min={1}
                                    max={4}
                                />
                            )}
                            
                            <SelectControl
                                label={__('Filter by Category', 'vitapro-appointments-fse')}
                                value={attributes.categoryId}
                                options={categoryOptions}
                                onChange={(value) => setAttributes({ categoryId: value })}
                            />
                            
                            <RangeControl
                                label={__('Number of Services (0 = all)', 'vitapro-appointments-fse')}
                                value={attributes.limit}
                                onChange={(value) => setAttributes({ limit: value })}
                                min={0}
                                max={20}
                            />
                            
                            <ToggleControl
                                label={__('Show Service Image', 'vitapro-appointments-fse')}
                                checked={attributes.showImage}
                                onChange={(value) => setAttributes({ showImage: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Description', 'vitapro-appointments-fse')}
                                checked={attributes.showDescription}
                                onChange={(value) => setAttributes({ showDescription: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Price', 'vitapro-appointments-fse')}
                                checked={attributes.showPrice}
                                onChange={(value) => setAttributes({ showPrice: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Duration', 'vitapro-appointments-fse')}
                                checked={attributes.showDuration}
                                onChange={(value) => setAttributes({ showDuration: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Book Button', 'vitapro-appointments-fse')}
                                checked={attributes.showBookButton}
                                onChange={(value) => setAttributes({ showBookButton: value })}
                            />
                            
                            <TextControl
                                label={__('Booking Form ID', 'vitapro-appointments-fse')}
                                value={attributes.bookingFormId}
                                onChange={(value) => setAttributes({ bookingFormId: value })}
                                help={__('ID of the booking form to link to when clicking book buttons.', 'vitapro-appointments-fse')}
                            />
                        </PanelBody>
                    </InspectorControls>
                    
                    <div {...blockProps}>
                        <div className="vpa-block-preview">
                            <div className="vpa-block-preview-title">
                                {__('Service List', 'vitapro-appointments-fse')}
                            </div>
                            <div className="vpa-block-preview-description">
                                {__('This block displays a list of available services with the following settings:', 'vitapro-appointments-fse')}
                            </div>
                            <ul className="vpa-block-preview-settings">
                                <li>
                                    <strong>{__('Layout:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.layout}
                                </li>
                                {attributes.layout === 'grid' && (
                                    <li>
                                        <strong>{__('Columns:', 'vitapro-appointments-fse')}</strong>{' '}
                                        {attributes.columns}
                                    </li>
                                )}
                                <li>
                                    <strong>{__('Filter by Category:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.categoryId ? categories.find(c => c.id.toString() === attributes.categoryId)?.name || attributes.categoryId : __('All Categories', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Number of Services:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.limit ? attributes.limit : __('All', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Service Image:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showImage ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Description:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showDescription ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Price:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showPrice ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Duration:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showDuration ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Book Button:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showBookButton ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                            </ul>
                        </div>
                    </div>
                </Fragment>
            );
        },
        
        save: function() {
            // Dynamic block, render on server
            return null;
        }
    });
    
    // Register Professional List Block
    registerBlockType('vitapro-appointments-fse/professional-list', {
        title: __('Professional List', 'vitapro-appointments-fse'),
        description: __('Display a list of available professionals.', 'vitapro-appointments-fse'),
        category: 'widgets',
        icon: 'groups',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            layout: {
                type: 'string',
                default: 'grid'
            },
            columns: {
                type: 'number',
                default: 3
            },
            showImage: {
                type: 'boolean',
                default: true
            },
            showBio: {
                type: 'boolean',
                default: true
            },
            showServices: {
                type: 'boolean',
                default: true
            },
            showBookButton: {
                type: 'boolean',
                default: true
            },
            serviceId: {
                type: 'string',
                default: ''
            },
            limit: {
                type: 'number',
                default: 0
            },
            bookingFormId: {
                type: 'string',
                default: ''
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps({
                className: 'vitapro-professional-list-wrapper'
            });
            
            // Get services from the global variable
            const services = window.vitaproBlockEditor?.services || [];
            
            // Convert services to options
            const serviceOptions = [
                { label: __('All Services', 'vitapro-appointments-fse'), value: '' },
                ...services.map(service => ({
                    label: service.title,
                    value: service.id.toString()
                }))
            ];
            
            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Professional List Settings', 'vitapro-appointments-fse')}>
                            <SelectControl
                                label={__('Layout', 'vitapro-appointments-fse')}
                                value={attributes.layout}
                                options={[
                                    { label: __('Grid', 'vitapro-appointments-fse'), value: 'grid' },
                                    { label: __('List', 'vitapro-appointments-fse'), value: 'list' }
                                ]}
                                onChange={(value) => setAttributes({ layout: value })}
                            />
                            
                            {attributes.layout === 'grid' && (
                                <RangeControl
                                    label={__('Columns', 'vitapro-appointments-fse')}
                                    value={attributes.columns}
                                    onChange={(value) => setAttributes({ columns: value })}
                                    min={1}
                                    max={4}
                                />
                            )}
                            
                            <SelectControl
                                label={__('Filter by Service', 'vitapro-appointments-fse')}
                                value={attributes.serviceId}
                                options={serviceOptions}
                                onChange={(value) => setAttributes({ serviceId: value })}
                            />
                            
                            <RangeControl
                                label={__('Number of Professionals (0 = all)', 'vitapro-appointments-fse')}
                                value={attributes.limit}
                                onChange={(value) => setAttributes({ limit: value })}
                                min={0}
                                max={20}
                            />
                            
                            <ToggleControl
                                label={__('Show Professional Image', 'vitapro-appointments-fse')}
                                checked={attributes.showImage}
                                onChange={(value) => setAttributes({ showImage: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Bio', 'vitapro-appointments-fse')}
                                checked={attributes.showBio}
                                onChange={(value) => setAttributes({ showBio: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Services', 'vitapro-appointments-fse')}
                                checked={attributes.showServices}
                                onChange={(value) => setAttributes({ showServices: value })}
                            />
                            
                            <ToggleControl
                                label={__('Show Book Button', 'vitapro-appointments-fse')}
                                checked={attributes.showBookButton}
                                onChange={(value) => setAttributes({ showBookButton: value })}
                            />
                            
                            <TextControl
                                label={__('Booking Form ID', 'vitapro-appointments-fse')}
                                value={attributes.bookingFormId}
                                onChange={(value) => setAttributes({ bookingFormId: value })}
                                help={__('ID of the booking form to link to when clicking book buttons.', 'vitapro-appointments-fse')}
                            />
                        </PanelBody>
                    </InspectorControls>
                    
                    <div {...blockProps}>
                        <div className="vpa-block-preview">
                            <div className="vpa-block-preview-title">
                                {__('Professional List', 'vitapro-appointments-fse')}
                            </div>
                            <div className="vpa-block-preview-description">
                                {__('This block displays a list of available professionals with the following settings:', 'vitapro-appointments-fse')}
                            </div>
                            <ul className="vpa-block-preview-settings">
                                <li>
                                    <strong>{__('Layout:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.layout}
                                </li>
                                {attributes.layout === 'grid' && (
                                    <li>
                                        <strong>{__('Columns:', 'vitapro-appointments-fse')}</strong>{' '}
                                        {attributes.columns}
                                    </li>
                                )}
                                <li>
                                    <strong>{__('Filter by Service:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.serviceId ? services.find(s => s.id.toString() === attributes.serviceId)?.title || attributes.serviceId : __('All Services', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Number of Professionals:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.limit ? attributes.limit : __('All', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Professional Image:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showImage ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Bio:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showBio ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Services:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showServices ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Show Book Button:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showBookButton ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                            </ul>
                        </div>
                    </div>
                </Fragment>
            );
        },
        
        save: function() {
            // Dynamic block, render on server
            return null;
        }
    });
    
    // Register Availability Calendar Block
    registerBlockType('vitapro-appointments-fse/availability-calendar', {
        title: __('Availability Calendar', 'vitapro-appointments-fse'),
        description: __('Display an availability calendar.', 'vitapro-appointments-fse'),
        category: 'widgets',
        icon: 'calendar',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            serviceId: {
                type: 'string',
                default: ''
            },
            professionalId: {
                type: 'string',
                default: ''
            },
            showLegend: {
                type: 'boolean',
                default: true
            },
            monthsToShow: {
                type: 'number',
                default: 1
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps({
                className: 'vitapro-availability-calendar-wrapper'
            });
            
            // Get services and professionals from the global variable
            const services = window.vitaproBlockEditor?.services || [];
            const professionals = window.vitaproBlockEditor?.professionals || [];
            
            // Convert services to options
            const serviceOptions = [
                { label: __('All Services', 'vitapro-appointments-fse'), value: '' },
                ...services.map(service => ({
                    label: service.title,
                    value: service.id.toString()
                }))
            ];
            
            // Convert professionals to options
            const professionalOptions = [
                { label: __('All Professionals', 'vitapro-appointments-fse'), value: '' },
                ...professionals.map(professional => ({
                    label: professional.title,
                    value: professional.id.toString()
                }))
            ];
            
            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Calendar Settings', 'vitapro-appointments-fse')}>
                            <SelectControl
                                label={__('Filter by Service', 'vitapro-appointments-fse')}
                                value={attributes.serviceId}
                                options={serviceOptions}
                                onChange={(value) => setAttributes({ serviceId: value })}
                            />
                            
                            <SelectControl
                                label={__('Filter by Professional', 'vitapro-appointments-fse')}
                                value={attributes.professionalId}
                                options={professionalOptions}
                                onChange={(value) => setAttributes({ professionalId: value })}
                            />
                            
                            <RangeControl
                                label={__('Months to Show', 'vitapro-appointments-fse')}
                                value={attributes.monthsToShow}
                                onChange={(value) => setAttributes({ monthsToShow: value })}
                                min={1}
                                max={6}
                            />
                            
                            <ToggleControl
                                label={__('Show Legend', 'vitapro-appointments-fse')}
                                checked={attributes.showLegend}
                                onChange={(value) => setAttributes({ showLegend: value })}
                            />
                        </PanelBody>
                    </InspectorControls>
                    
                    <div {...blockProps}>
                        <div className="vpa-block-preview">
                            <div className="vpa-block-preview-title">
                                {__('Availability Calendar', 'vitapro-appointments-fse')}
                            </div>
                            <div className="vpa-block-preview-description">
                                {__('This block displays an availability calendar with the following settings:', 'vitapro-appointments-fse')}
                            </div>
                            <ul className="vpa-block-preview-settings">
                                <li>
                                    <strong>{__('Filter by Service:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.serviceId ? services.find(s => s.id.toString() === attributes.serviceId)?.title || attributes.serviceId : __('All Services', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Filter by Professional:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.professionalId ? professionals.find(p => p.id.toString() === attributes.professionalId)?.title || attributes.professionalId : __('All Professionals', 'vitapro-appointments-fse')}
                                </li>
                                <li>
                                    <strong>{__('Months to Show:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.monthsToShow}
                                </li>
                                <li>
                                    <strong>{__('Show Legend:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showLegend ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                            </ul>
                        </div>
                    </div>
                </Fragment>
            );
        },
        
        save: function() {
            // Dynamic block, render on server
            return null;
        }
    });
    
    // Register My Appointments Block
    registerBlockType('vitapro-appointments-fse/my-appointments', {
        title: __('My Appointments', 'vitapro-appointments-fse'),
        description: __('Display user appointments (requires login).', 'vitapro-appointments-fse'),
        category: 'widgets',
        icon: 'admin-users',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        attributes: {
            showUpcoming: {
                type: 'boolean',
                default: true
            },
            showPast: {
                type: 'boolean',
                default: true
            },
            allowCancellation: {
                type: 'boolean',
                default: true
            },
            upcomingLimit: {
                type: 'number',
                default: 10
            },
            pastLimit: {
                type: 'number',
                default: 10
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps({
                className: 'vitapro-my-appointments-wrapper'
            });
            
            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('My Appointments Settings', 'vitapro-appointments-fse')}>
                            <ToggleControl
                                label={__('Show Upcoming Appointments', 'vitapro-appointments-fse')}
                                checked={attributes.showUpcoming}
                                onChange={(value) => setAttributes({ showUpcoming: value })}
                            />
                            
                            {attributes.showUpcoming && (
                                <RangeControl
                                    label={__('Upcoming Appointments Limit', 'vitapro-appointments-fse')}
                                    value={attributes.upcomingLimit}
                                    onChange={(value) => setAttributes({ upcomingLimit: value })}
                                    min={1}
                                    max={50}
                                />
                            )}
                            
                            <ToggleControl
                                label={__('Show Past Appointments', 'vitapro-appointments-fse')}
                                checked={attributes.showPast}
                                onChange={(value) => setAttributes({ showPast: value })}
                            />
                            
                            {attributes.showPast && (
                                <RangeControl
                                    label={__('Past Appointments Limit', 'vitapro-appointments-fse')}
                                    value={attributes.pastLimit}
                                    onChange={(value) => setAttributes({ pastLimit: value })}
                                    min={1}
                                    max={50}
                                />
                            )}
                            
                            <ToggleControl
                                label={__('Allow Cancellation', 'vitapro-appointments-fse')}
                                checked={attributes.allowCancellation}
                                onChange={(value) => setAttributes({ allowCancellation: value })}
                            />
                        </PanelBody>
                    </InspectorControls>
                    
                    <div {...blockProps}>
                        <div className="vpa-block-preview">
                            <div className="vpa-block-preview-title">
                                {__('My Appointments', 'vitapro-appointments-fse')}
                            </div>
                            <div className="vpa-block-preview-description">
                                {__('This block displays user appointments (requires login) with the following settings:', 'vitapro-appointments-fse')}
                            </div>
                            <ul className="vpa-block-preview-settings">
                                <li>
                                    <strong>{__('Show Upcoming Appointments:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showUpcoming ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                {attributes.showUpcoming && (
                                    <li>
                                        <strong>{__('Upcoming Appointments Limit:', 'vitapro-appointments-fse')}</strong>{' '}
                                        {attributes.upcomingLimit}
                                    </li>
                                )}
                                <li>
                                    <strong>{__('Show Past Appointments:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.showPast ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                                {attributes.showPast && (
                                    <li>
                                        <strong>{__('Past Appointments Limit:', 'vitapro-appointments-fse')}</strong>{' '}
                                        {attributes.pastLimit}
                                    </li>
                                )}
                                <li>
                                    <strong>{__('Allow Cancellation:', 'vitapro-appointments-fse')}</strong>{' '}
                                    {attributes.allowCancellation ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse')}
                                </li>
                            </ul>
                        </div>
                    </div>
                </Fragment>
            );
        },
        
        save: function() {
            // Dynamic block, render on server
            return null;
        }
    });
    
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n);