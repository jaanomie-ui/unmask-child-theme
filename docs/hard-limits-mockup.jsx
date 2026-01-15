import React, { useState } from 'react';

const CATEGORIES = {
  'physical-touch': {
    label: 'PHYSICAL TOUCH & SENSATION',
    activities: [
      'LIGHT TOUCH', 'SCRATCHING', 'BITING', 'PINCHING',
      'HAIR PULLING', 'TEMPERATURE PLAY', 'TICKLING', 'MASSAGE'
    ]
  },
  'bondage': {
    label: 'BONDAGE & RESTRAINT',
    activities: [
      'ROPE BONDAGE', 'LEATHER CUFFS', 'METAL CUFFS', 'SPREADER BARS',
      'SUSPENSION', 'MUMMIFICATION', 'PREDICAMENT', 'HOGTIE'
    ]
  },
  'impact': {
    label: 'IMPACT PLAY',
    activities: [
      'HAND SPANKING', 'PADDLE', 'FLOGGER', 'CROP',
      'CANE', 'WHIP', 'BELT', 'FACE SLAPPING'
    ]
  },
  'power-exchange': {
    label: 'POWER EXCHANGE & SERVICE',
    activities: [
      'VERBAL COMMANDS', 'POSITIONS', 'PROTOCOLS', 'DOMESTIC SERVICE',
      'BODY SERVICE', 'BOOT WORSHIP', 'OBJECTIFICATION', 'PET PLAY'
    ]
  },
  'sensory': {
    label: 'SENSORY PLAY',
    activities: [
      'BLINDFOLDS', 'EARPLUGS', 'HOODS', 'GAGS',
      'SENSORY DEPRIVATION', 'ELECTRICAL PLAY', 'NEEDLE PLAY'
    ]
  },
  'psychological': {
    label: 'ROLE PLAY & PSYCHOLOGICAL',
    activities: [
      'HUMILIATION', 'DEGRADATION', 'PRAISE KINK', 'FEAR PLAY',
      'INTERROGATION', 'AGE PLAY', 'CNC', 'HYPNOSIS'
    ]
  },
  'exhibition': {
    label: 'PUBLIC & EXHIBITION',
    activities: [
      'BEING WATCHED', 'WATCHING OTHERS', 'PUBLIC COLLAR',
      'EVENT PLAY', 'PHOTOGRAPHY', 'VIDEO'
    ]
  },
  'fetish': {
    label: 'BODY WORSHIP & FETISH',
    activities: [
      'FOOT WORSHIP', 'HAND WORSHIP', 'LEATHER', 'LATEX',
      'UNIFORMS', 'BODY MODIFICATION'
    ]
  },
  'edge': {
    label: 'EDGE PLAY & RISK-AWARE',
    activities: [
      'BREATH PLAY', 'KNIFE PLAY', 'FIRE PLAY', 'BLOOD PLAY',
      'BRANDING', 'PIERCING'
    ]
  },
  'aftercare': {
    label: 'INTIMACY & AFTERCARE',
    activities: [
      'CUDDLING', 'KISSING', 'VERBAL PROCESSING', 'PHYSICAL AFTERCARE',
      'ALONE TIME', 'FOOD/DRINK CARE'
    ]
  }
};

const COLORS = {
  green: '#4ade80',
  yellow: '#8B7355',
  red: '#8a3233',
  void: '#181818',
  card: '#1c1c1c',
  border: '#2e2e2e',
  textPrimary: '#e5e5e5',
  textSecondary: '#8e8e8e',
  textMuted: '#636363'
};

export default function HardLimitsChecklist() {
  const [selections, setSelections] = useState({});
  const [collapsed, setCollapsed] = useState({});

  const handleSelect = (activity, level) => {
    setSelections(prev => {
      const slug = activity.toLowerCase().replace(/[^a-z0-9]/g, '-');
      if (prev[slug] === level) {
        const newState = { ...prev };
        delete newState[slug];
        return newState;
      }
      return { ...prev, [slug]: level };
    });
  };

  const getSelection = (activity) => {
    const slug = activity.toLowerCase().replace(/[^a-z0-9]/g, '-');
    return selections[slug];
  };

  const toggleCategory = (catKey) => {
    setCollapsed(prev => ({ ...prev, [catKey]: !prev[catKey] }));
  };

  const toggleAll = () => {
    const allCollapsed = Object.keys(CATEGORIES).every(k => collapsed[k]);
    const newState = {};
    Object.keys(CATEGORIES).forEach(k => {
      newState[k] = !allCollapsed;
    });
    setCollapsed(newState);
  };

  const counts = {
    green: Object.values(selections).filter(v => v === 'green').length,
    yellow: Object.values(selections).filter(v => v === 'yellow').length,
    red: Object.values(selections).filter(v => v === 'red').length
  };

  const allCollapsed = Object.keys(CATEGORIES).every(k => collapsed[k]);

  return (
    <div style={{
      background: COLORS.void,
      minHeight: '100vh',
      fontFamily: "'Berkeley Mono', 'SF Mono', Monaco, monospace",
      color: COLORS.textPrimary
    }}>
      <div style={{
        maxWidth: 800,
        margin: '0 auto',
        padding: '48px 24px 140px'
      }}>
        {/* Header */}
        <header style={{ textAlign: 'center', marginBottom: 48 }}>
          <h1 style={{
            fontSize: 28,
            fontWeight: 400,
            color: COLORS.textPrimary,
            marginBottom: 12,
            letterSpacing: '0.02em',
            textTransform: 'lowercase'
          }}>
            hard limits
          </h1>
          <p style={{
            color: COLORS.textSecondary,
            fontSize: 13,
            maxWidth: 500,
            margin: '0 auto',
            lineHeight: 1.6
          }}>
            define your boundaries. green means yes, yellow means maybe, red means no.
            your limits are visible only to connections.
          </p>
        </header>

        {/* Legend */}
        <div style={{
          display: 'flex',
          justifyContent: 'center',
          gap: 32,
          marginBottom: 32,
          padding: '16px 24px',
          background: COLORS.card,
          borderRadius: 8,
          border: `1px solid ${COLORS.border}`,
          position: 'sticky',
          top: 0,
          zIndex: 50
        }}>
          {[
            { color: 'green', label: 'YES / INTO IT' },
            { color: 'yellow', label: 'MAYBE / NEGOTIATE' },
            { color: 'red', label: 'HARD NO' }
          ].map(item => (
            <div key={item.color} style={{
              display: 'flex',
              alignItems: 'center',
              gap: 8,
              fontSize: 11,
              color: COLORS.textSecondary,
              textTransform: 'uppercase',
              letterSpacing: '0.05em'
            }}>
              <span style={{
                width: 14,
                height: 14,
                borderRadius: '50%',
                background: COLORS[item.color]
              }} />
              <span>{item.label}</span>
            </div>
          ))}
        </div>

        {/* Expand/Collapse All */}
        <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 16 }}>
          <button
            onClick={toggleAll}
            style={{
              fontSize: 11,
              color: COLORS.textMuted,
              background: 'none',
              border: 'none',
              cursor: 'pointer',
              padding: '4px 8px',
              textTransform: 'lowercase',
              fontFamily: 'inherit'
            }}
          >
            {allCollapsed ? 'expand all' : 'collapse all'}
          </button>
        </div>

        {/* Categories */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {Object.entries(CATEGORIES).map(([catKey, category]) => {
            const isCollapsed = collapsed[catKey];
            const catCount = category.activities.filter(a => getSelection(a)).length;

            return (
              <section key={catKey} style={{
                background: COLORS.card,
                borderRadius: 8,
                border: `1px solid ${COLORS.border}`,
                overflow: 'hidden'
              }}>
                {/* Category Header */}
                <header
                  onClick={() => toggleCategory(catKey)}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 12,
                    padding: '16px 20px',
                    cursor: 'pointer',
                    userSelect: 'none'
                  }}
                >
                  <span style={{
                    width: 20,
                    height: 20,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: COLORS.textMuted,
                    transform: isCollapsed ? 'rotate(-90deg)' : 'rotate(0)',
                    transition: 'transform 0.2s ease'
                  }}>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <polyline points="6 9 12 15 18 9" />
                    </svg>
                  </span>
                  <h2 style={{
                    fontSize: 12,
                    fontWeight: 500,
                    color: COLORS.textSecondary,
                    margin: 0,
                    flex: 1,
                    letterSpacing: '0.05em'
                  }}>
                    {category.label}
                  </h2>
                  <span style={{
                    fontSize: 11,
                    color: COLORS.textMuted,
                    background: COLORS.void,
                    padding: '4px 10px',
                    borderRadius: 12
                  }}>
                    {catCount}/{category.activities.length}
                  </span>
                </header>

                {/* Category Body */}
                <div style={{
                  maxHeight: isCollapsed ? 0 : 2000,
                  overflow: 'hidden',
                  padding: isCollapsed ? '0 20px' : '0 20px 16px',
                  transition: 'all 0.3s ease'
                }}>
                  <div style={{ display: 'grid', gap: 4 }}>
                    {category.activities.map(activity => {
                      const selected = getSelection(activity);

                      return (
                        <div key={activity} style={{
                          display: 'flex',
                          alignItems: 'center',
                          gap: 16,
                          padding: '10px 0',
                          borderBottom: `1px solid ${COLORS.border}`
                        }}>
                          <span style={{
                            flex: 1,
                            fontSize: 12,
                            color: COLORS.textSecondary,
                            letterSpacing: '0.03em'
                          }}>
                            {activity}
                          </span>

                          {/* Traffic Light Circles */}
                          <div style={{ display: 'flex', gap: 6 }}>
                            {['green', 'yellow', 'red'].map(level => {
                              const isActive = selected === level;
                              return (
                                <button
                                  key={level}
                                  onClick={() => handleSelect(activity, level)}
                                  style={{
                                    width: 28,
                                    height: 28,
                                    borderRadius: '50%',
                                    border: `2px solid ${isActive ? COLORS[level] : COLORS.border}`,
                                    background: isActive ? COLORS[level] : 'transparent',
                                    cursor: 'pointer',
                                    transition: 'all 0.15s ease',
                                    boxShadow: isActive ? `0 0 16px ${COLORS[level]}40` : 'none',
                                    position: 'relative'
                                  }}
                                  onMouseEnter={(e) => {
                                    if (!isActive) {
                                      e.target.style.borderColor = COLORS[level];
                                      e.target.style.transform = 'scale(1.1)';
                                    }
                                  }}
                                  onMouseLeave={(e) => {
                                    if (!isActive) {
                                      e.target.style.borderColor = COLORS.border;
                                      e.target.style.transform = 'scale(1)';
                                    }
                                  }}
                                />
                              );
                            })}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              </section>
            );
          })}
        </div>
      </div>

      {/* Sticky Action Bar */}
      <div style={{
        position: 'fixed',
        bottom: 0,
        left: 0,
        right: 0,
        display: 'flex',
        justifyContent: 'center',
        padding: '16px 24px',
        background: `linear-gradient(to top, ${COLORS.void} 0%, ${COLORS.void} 80%, transparent 100%)`,
        zIndex: 100
      }}>
        <div style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          gap: 24,
          maxWidth: 800,
          width: '100%',
          padding: '12px 20px',
          background: COLORS.card,
          border: `1px solid ${COLORS.border}`,
          borderRadius: 8
        }}>
          {/* Counts */}
          <div style={{ display: 'flex', gap: 20, fontSize: 12 }}>
            {[
              { color: 'green', label: 'yes' },
              { color: 'yellow', label: 'maybe' },
              { color: 'red', label: 'no' }
            ].map(item => (
              <div key={item.color} style={{
                display: 'flex',
                alignItems: 'center',
                gap: 6,
                color: COLORS.textMuted
              }}>
                <span style={{
                  fontWeight: 600,
                  color: COLORS[item.color],
                  fontVariantNumeric: 'tabular-nums',
                  minWidth: 20
                }}>
                  {counts[item.color]}
                </span>
                <span>{item.label}</span>
              </div>
            ))}
          </div>

          {/* Buttons */}
          <div style={{ display: 'flex', gap: 8 }}>
            <button style={{
              padding: '8px 16px',
              borderRadius: 6,
              fontSize: 12,
              fontWeight: 500,
              cursor: 'pointer',
              border: 'none',
              background: 'transparent',
              color: COLORS.textMuted,
              fontFamily: 'inherit',
              textTransform: 'lowercase'
            }}>
              clear
            </button>
            <button style={{
              padding: '8px 16px',
              borderRadius: 6,
              fontSize: 12,
              fontWeight: 500,
              cursor: 'pointer',
              border: `1px solid ${COLORS.border}`,
              background: COLORS.void,
              color: COLORS.textSecondary,
              fontFamily: 'inherit',
              textTransform: 'lowercase'
            }}>
              share
            </button>
            <button style={{
              padding: '8px 16px',
              borderRadius: 6,
              fontSize: 12,
              fontWeight: 500,
              cursor: 'pointer',
              border: 'none',
              background: COLORS.red,
              color: COLORS.textPrimary,
              fontFamily: 'inherit',
              textTransform: 'lowercase'
            }}>
              save
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
