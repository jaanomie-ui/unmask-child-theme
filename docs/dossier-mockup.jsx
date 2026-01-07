import React, { useState } from 'react';

const DossierNotebook = () => {
  // UNMASK Design Tokens — HEAVILY MUTED
  const t = {
    bgPage: '#131313',
    bgCard: '#171717',
    bgField: '#151515',
    textPrimary: '#a8a8a8',
    textSecondary: '#707070',
    textMuted: '#454545',
    borderHeavy: '#383838',
    borderDefault: '#282828',
    borderSubtle: '#1e1e1e',
    sectionBg: '#1a1a1a',

    // Visibility colors — VERY DESATURATED
    green: '#4a5550',
    greenDark: '#3a4340',
    greenTint: '#252a28',

    yellow: '#6a6350',
    yellowDark: '#4a4538',
    yellowTint: '#252420',

    red: '#5a4548',
    redDark: '#453538',
    redTint: '#231e20',

    gray: '#3a3a3a',
    grayDark: '#2a2a2a',
    grayTint: '#1c1c1c',
  };

  const mono = { fontFamily: '"Berkeley Mono", "SF Mono", monospace' };
  const CIRCLE_SIZE = 16;  // ALL CIRCLES SAME SIZE
  const MIN_CHARS = 2;

  const visibilityOrder = ['green', 'yellow', 'red'];
  const visibilityMeta = {
    green: { color: t.green, dark: t.greenDark, tint: t.greenTint, label: 'interlinked' },
    yellow: { color: t.yellow, dark: t.yellowDark, tint: t.yellowTint, label: 'user' },
    red: { color: t.red, dark: t.redDark, tint: t.redTint, label: 'visitor' },
    gray: { color: t.gray, dark: t.grayDark, tint: t.grayTint, label: 'hidden' },
  };

  // ALL FIELDS DEFAULT TO HIDDEN
  const [sections, setSections] = useState([
    {
      id: 'identity',
      label: 'identity',
      locked: null,
      fields: [
        { id: 'scene_name', label: 'scene name', value: '', visibility: 'hidden' },
        { id: 'designation', label: 'designation', value: 'V-026', visibility: 'hidden', editable: false },
        { id: 'pronouns', label: 'pronouns', value: '', visibility: 'hidden' },
        { id: 'city', label: 'city', value: '', visibility: 'hidden' },
      ]
    },
    {
      id: 'creative',
      label: 'creative practice',
      locked: null,
      fields: [
        { id: 'functions', label: 'functions', value: '', visibility: 'hidden' },
        { id: 'availability', label: 'availability', value: '', visibility: 'hidden' },
        { id: 'portfolio', label: 'portfolio link', value: '', visibility: 'hidden' },
        { id: 'bio', label: 'bio', value: '', visibility: 'hidden' },
      ]
    },
    {
      id: 'kink',
      label: 'kink profile',
      locked: null,
      fields: [
        { id: 'orientation', label: 'd/s orientation', value: '', visibility: 'hidden' },
        { id: 'interests', label: 'interests', value: '', visibility: 'hidden' },
        { id: 'limits', label: 'limits', value: '', visibility: 'hidden' },
        { id: 'looking_for', label: 'looking for', value: '', visibility: 'hidden' },
      ]
    },
    {
      id: 'custom',
      label: 'custom fields',
      locked: null,
      isCustom: true,
      fields: []
    },
  ]);

  // Custom field creation state — BOTH FIELDS OPEN
  const [newLabel, setNewLabel] = useState('');
  const [newValue, setNewValue] = useState('');
  const [errors, setErrors] = useState({ label: false, value: false });

  // Red Triangle Error Indicator
  const ErrorTriangle = ({ show, rule }) => {
    if (!show) return null;
    return (
      <span
        title={rule}
        style={{
          display: 'inline-block',
          width: 0,
          height: 0,
          borderLeft: '5px solid transparent',
          borderRight: '5px solid transparent',
          borderBottom: `8px solid ${t.red}`,
          marginLeft: '4px',
          cursor: 'help',
        }}
      />
    );
  };

  // Circle component — ALL SAME SIZE
  const Circle = ({ color, active, onClick, disabled = false }) => {
    const meta = visibilityMeta[color] || visibilityMeta.gray;
    return (
      <button
        onClick={disabled ? undefined : onClick}
        disabled={disabled}
        style={{
          width: CIRCLE_SIZE,
          height: CIRCLE_SIZE,
          borderRadius: '50%',
          backgroundColor: active ? meta.dark : 'transparent',
          border: `1px solid ${active ? meta.color : t.borderSubtle}`,
          cursor: disabled ? 'default' : 'pointer',
          padding: 0,
          opacity: disabled ? 0.3 : 1,
          flexShrink: 0,
        }}
      />
    );
  };

  const setSectionLock = (sectionId, color) => {
    setSections(sections.map(s => {
      if (s.id === sectionId) {
        if (color === 'gray' || color === null) {
          return { ...s, locked: null };
        } else {
          return {
            ...s,
            locked: color,
            fields: s.fields.map(f => ({
              ...f,
              visibility: f.visibility === 'hidden' ? 'hidden' : color
            }))
          };
        }
      }
      return s;
    }));
  };

  const setFieldVisibility = (sectionId, fieldId, newVisibility) => {
    setSections(sections.map(s => {
      if (s.id === sectionId && s.locked === null) {
        return {
          ...s,
          fields: s.fields.map(f => {
            if (f.id === fieldId) {
              return { ...f, visibility: newVisibility };
            }
            return f;
          })
        };
      }
      return s;
    }));
  };

  const updateFieldValue = (sectionId, fieldId, newValue) => {
    setSections(sections.map(s => {
      if (s.id === sectionId) {
        return {
          ...s,
          fields: s.fields.map(f => f.id === fieldId ? { ...f, value: newValue } : f)
        };
      }
      return s;
    }));
  };

  // Validate and add custom field
  const addCustomField = () => {
    const labelError = newLabel.length < MIN_CHARS;
    const valueError = newValue.length < MIN_CHARS;

    setErrors({ label: labelError, value: valueError });

    if (labelError || valueError) return;

    setSections(sections.map(s => {
      if (s.id === 'custom') {
        return {
          ...s,
          fields: [...s.fields, {
            id: `custom_${Date.now()}`,
            label: newLabel.toLowerCase(),
            value: newValue,
            visibility: 'hidden',  // DEFAULT TO HIDDEN
            userCreated: true,
          }]
        };
      }
      return s;
    }));

    setNewLabel('');
    setNewValue('');
    setErrors({ label: false, value: false });
  };

  const deleteCustomField = (fieldId) => {
    setSections(sections.map(s => {
      if (s.id === 'custom') {
        return { ...s, fields: s.fields.filter(f => f.id !== fieldId) };
      }
      return s;
    }));
  };

  const getEffectiveVisibility = (section, field) => {
    if (field.visibility === 'hidden') return 'gray';
    if (section.locked) return section.locked;
    return field.visibility;
  };

  return (
    <div style={{
      backgroundColor: t.bgPage,
      minHeight: '100vh',
      ...mono,
      color: t.textPrimary,
    }}>

      {/* Header */}
      <div style={{
        padding: '16px 20px',
        borderBottom: `1px solid ${t.borderHeavy}`,
      }}>
        <span style={{
          fontSize: '11px',
          textTransform: 'lowercase',
          letterSpacing: '0.1em',
          color: t.textSecondary,
        }}>
          :: dossier
        </span>
      </div>

      {/* Legend */}
      <div style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(4, 1fr)',
        gap: '1px',
        backgroundColor: t.borderDefault,
        borderBottom: `1px solid ${t.borderHeavy}`,
      }}>
        {['green', 'yellow', 'red', 'gray'].map(color => {
          const meta = visibilityMeta[color];
          return (
            <div key={color} style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '6px',
              padding: '8px',
              backgroundColor: t.bgCard,
            }}>
              <div style={{
                width: '8px',
                height: '8px',
                borderRadius: '50%',
                backgroundColor: meta.dark,
                border: `1px solid ${meta.color}`,
              }} />
              <span style={{ fontSize: '9px', color: t.textMuted }}>
                {meta.label}
              </span>
            </div>
          );
        })}
      </div>

      {/* Sections */}
      <div style={{ paddingBottom: '100px' }}>
        {sections.map((section) => {
          const isOpen = section.locked === null;

          return (
            <div key={section.id}>

              {/* Section Header */}
              <div style={{
                display: 'grid',
                gridTemplateColumns: '1fr auto',
                alignItems: 'center',
                padding: '10px 20px',
                backgroundColor: t.sectionBg,
                borderBottom: `1px solid ${t.borderDefault}`,
              }}>
                <span style={{
                  fontSize: '10px',
                  textTransform: 'uppercase',
                  letterSpacing: '0.12em',
                  color: t.textSecondary,
                }}>
                  {section.label}
                  {isOpen && (
                    <span style={{
                      marginLeft: '8px',
                      fontSize: '9px',
                      color: t.textMuted,
                      fontStyle: 'italic',
                    }}>
                      (editing)
                    </span>
                  )}
                </span>

                <div style={{ display: 'flex', gap: '6px', alignItems: 'center' }}>
                  <Circle
                    color="gray"
                    active={isOpen}
                    onClick={() => setSectionLock(section.id, 'gray')}
                  />
                  <div style={{ width: '1px', height: '10px', backgroundColor: t.borderSubtle, margin: '0 2px' }} />
                  {visibilityOrder.map(color => (
                    <Circle
                      key={color}
                      color={color}
                      active={section.locked === color}
                      onClick={() => setSectionLock(section.id, color)}
                    />
                  ))}
                </div>
              </div>

              {/* Fields */}
              {section.fields.map((field) => {
                const effectiveVis = getEffectiveVisibility(section, field);
                const isHidden = field.visibility === 'hidden';
                const meta = visibilityMeta[effectiveVis];

                return (
                  <div
                    key={field.id}
                    style={{
                      display: 'grid',
                      gridTemplateColumns: '120px 1fr 88px',
                      alignItems: 'center',
                      padding: '8px 20px',
                      backgroundColor: isHidden ? t.grayTint : t.bgField,
                      borderBottom: `1px solid ${t.borderSubtle}`,
                      borderLeft: `3px solid ${meta.color}`,
                    }}
                  >
                    {/* Label */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                      {field.userCreated && (
                        <button
                          onClick={() => deleteCustomField(field.id)}
                          style={{
                            background: 'none',
                            border: 'none',
                            color: t.red,
                            cursor: 'pointer',
                            fontSize: '10px',
                            padding: 0,
                          }}
                        >
                          x
                        </button>
                      )}
                      <span style={{
                        fontSize: '9px',
                        textTransform: 'uppercase',
                        letterSpacing: '0.08em',
                        color: t.textMuted,
                        textDecoration: isHidden ? 'line-through' : 'none',
                      }}>
                        {field.label}
                      </span>
                    </div>

                    {/* Value */}
                    <div style={{ paddingRight: '12px' }}>
                      {field.editable === false ? (
                        <span style={{
                          fontSize: '12px',
                          color: isHidden ? t.textMuted : t.textPrimary,
                          textDecoration: isHidden ? 'line-through' : 'none',
                        }}>
                          {field.value}
                        </span>
                      ) : (
                        <input
                          type="text"
                          value={field.value}
                          onChange={(e) => updateFieldValue(section.id, field.id, e.target.value)}
                          placeholder="..."
                          style={{
                            width: '100%',
                            backgroundColor: 'transparent',
                            border: 'none',
                            borderBottom: `1px solid ${isHidden ? 'transparent' : t.borderDefault}`,
                            color: isHidden ? t.textMuted : t.textPrimary,
                            fontSize: '12px',
                            padding: '2px 0',
                            textDecoration: isHidden ? 'line-through' : 'none',
                            ...mono,
                          }}
                        />
                      )}
                    </div>

                    {/* Visibility circles — ALL SAME SIZE */}
                    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '4px' }}>
                      <Circle
                        color="gray"
                        active={field.visibility === 'hidden'}
                        onClick={() => setFieldVisibility(section.id, field.id, 'hidden')}
                        disabled={!isOpen}
                      />
                      {visibilityOrder.map(color => (
                        <Circle
                          key={color}
                          color={color}
                          active={effectiveVis === color && field.visibility !== 'hidden'}
                          onClick={() => setFieldVisibility(section.id, field.id, color)}
                          disabled={!isOpen}
                        />
                      ))}
                    </div>
                  </div>
                );
              })}

              {/* Add Custom Field — BOTH FIELDS OPEN */}
              {section.isCustom && (
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: '120px 1fr 88px',
                  alignItems: 'center',
                  padding: '8px 20px',
                  backgroundColor: t.bgCard,
                  borderBottom: `1px solid ${t.borderDefault}`,
                  borderLeft: `3px solid ${t.borderSubtle}`,
                }}>
                  {/* Label input with error */}
                  <div style={{ display: 'flex', alignItems: 'center' }}>
                    <input
                      type="text"
                      value={newLabel}
                      onChange={(e) => {
                        setNewLabel(e.target.value);
                        if (errors.label && e.target.value.length >= MIN_CHARS) {
                          setErrors(prev => ({ ...prev, label: false }));
                        }
                      }}
                      placeholder="label"
                      style={{
                        width: '90px',
                        backgroundColor: 'transparent',
                        border: 'none',
                        borderBottom: `1px solid ${errors.label ? t.red : t.borderDefault}`,
                        color: t.textPrimary,
                        fontSize: '9px',
                        textTransform: 'uppercase',
                        letterSpacing: '0.08em',
                        padding: '2px 0',
                        ...mono,
                      }}
                    />
                    <ErrorTriangle show={errors.label} rule={`min_char_${MIN_CHARS}`} />
                  </div>

                  {/* Value input with error */}
                  <div style={{ display: 'flex', alignItems: 'center', paddingRight: '12px' }}>
                    <input
                      type="text"
                      value={newValue}
                      onChange={(e) => {
                        setNewValue(e.target.value);
                        if (errors.value && e.target.value.length >= MIN_CHARS) {
                          setErrors(prev => ({ ...prev, value: false }));
                        }
                      }}
                      placeholder="value..."
                      onKeyPress={(e) => e.key === 'Enter' && addCustomField()}
                      style={{
                        flex: 1,
                        backgroundColor: 'transparent',
                        border: 'none',
                        borderBottom: `1px solid ${errors.value ? t.red : t.borderDefault}`,
                        color: t.textPrimary,
                        fontSize: '12px',
                        padding: '2px 0',
                        ...mono,
                      }}
                    />
                    <ErrorTriangle show={errors.value} rule={`min_char_${MIN_CHARS}`} />
                  </div>

                  {/* Add button */}
                  <button
                    onClick={addCustomField}
                    style={{
                      padding: '4px 8px',
                      backgroundColor: 'transparent',
                      border: `1px solid ${t.borderDefault}`,
                      color: t.textSecondary,
                      fontSize: '9px',
                      cursor: 'pointer',
                      justifySelf: 'end',
                      ...mono,
                    }}
                  >
                    [add]
                  </button>
                </div>
              )}
            </div>
          );
        })}
      </div>

      {/* Bottom Preview */}
      <div style={{
        position: 'fixed',
        bottom: 0,
        left: 0,
        right: 0,
        backgroundColor: t.bgCard,
        borderTop: `1px solid ${t.borderHeavy}`,
      }}>
        <div style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(4, 1fr)',
          gap: '1px',
          backgroundColor: t.borderDefault,
        }}>
          {['green', 'yellow', 'red', 'gray'].map(viewLevel => {
            const meta = visibilityMeta[viewLevel];
            const count = viewLevel === 'gray'
              ? sections.reduce((acc, s) => acc + s.fields.filter(f => f.visibility === 'hidden').length, 0)
              : sections.reduce((acc, s) => {
                  return acc + s.fields.filter(f => {
                    if (f.visibility === 'hidden') return false;
                    const vis = getEffectiveVisibility(s, f);
                    if (viewLevel === 'green') return true;
                    if (viewLevel === 'yellow') return vis === 'yellow' || vis === 'red';
                    if (viewLevel === 'red') return vis === 'red';
                    return false;
                  }).length;
                }, 0);

            return (
              <div key={viewLevel} style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '6px',
                padding: '12px 8px',
                backgroundColor: t.bgCard,
              }}>
                <div style={{
                  width: '20px',
                  height: '20px',
                  borderRadius: '50%',
                  backgroundColor: meta.dark,
                  border: `1px solid ${meta.color}`,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: '10px',
                  color: t.textPrimary,
                }}>
                  {count}
                </div>
                <span style={{ fontSize: '9px', color: t.textMuted }}>
                  {meta.label}
                </span>
              </div>
            );
          })}
        </div>
      </div>

    </div>
  );
};

export default DossierNotebook;
