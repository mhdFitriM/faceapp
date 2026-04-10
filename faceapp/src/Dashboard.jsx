import './Dashboard.css'

const STATUS_CONFIG = {
  active: { label: 'Active', color: 'green' },
  pending: { label: 'Syncing', color: 'amber' },
  inactive: { label: 'Inactive', color: 'red' },
}

const SYNC_STATUS_CONFIG = {
  verified: 'green',
  synced: 'blue',
  pending: 'amber',
  failed: 'red',
  deleted: 'red',
}

function formatActivityTime(value) {
  if (!value) {
    return 'Pending'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return value
  }

  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

export default function Dashboard({
  user,
  users,
  activeDevices,
  loading,
  refreshing,
  onSelectUser,
  onOpenCamera,
}) {
  const status = STATUS_CONFIG[user?.status] || STATUS_CONFIG.pending
  const initials = user?.name
    ? user.name.split(' ').map((name) => name[0]).join('').slice(0, 2)
    : 'NA'

  return (
    <div className="dashboard">
      <div className="db-blob db-blob-1" aria-hidden="true" />
      <div className="db-blob db-blob-2" aria-hidden="true" />

      <header className="db-header glass animate-fadeUp">
        <div className="db-logo">
          <div className="db-logo-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M12 2a4 4 0 014 4v2a4 4 0 01-8 0V6a4 4 0 014-4z" />
              <path d="M6.5 10.5C5 12 4 14 4 16c0 3.3 3.6 6 8 6s8-2.7 8-6c0-2-.9-3.8-2.3-5.1" />
            </svg>
          </div>
          <span>FaceID</span>
        </div>
        <div className="db-header-right">
          <div className={`status-badge status-${status.color}`}>
            <span className="status-dot" />
            {refreshing ? 'Refreshing' : status.label}
          </div>
        </div>
      </header>

      <main className="db-main">
        <section className="selector-card glass animate-fadeUp">
          <div className="selector-copy">
            <p className="selector-label">Managed User</p>
            <h2>{user ? user.name : 'Choose a user from admin'}</h2>
            <p>{user ? 'Face enrollment and recent events now come from the Laravel admin records.' : 'Create a user in the admin panel first, then come back here to capture the face.'}</p>
          </div>

          <label className="selector-field" htmlFor="managed-user-select">
            <span>Select user</span>
            <select
              id="managed-user-select"
              value={user?.id ?? ''}
              onChange={(event) => onSelectUser(event.target.value)}
              disabled={loading || users.length === 0}
            >
              {users.length === 0 ? (
                <option value="">No users available</option>
              ) : (
                users.map((managedUser) => (
                  <option key={managedUser.id} value={managedUser.id}>
                    {managedUser.name} - {managedUser.employeeId}
                  </option>
                ))
              )}
            </select>
          </label>
        </section>

        {!user ? (
          <section className="empty-state glass animate-fadeUp">
            <h3>No managed user is ready yet</h3>
            <p>Add a user from the Laravel admin panel, then this FaceApp screen will let you enroll that user across every active device.</p>
          </section>
        ) : (
          <>
            <section className="profile-hero animate-fadeUp" style={{ animationDelay: '0.05s' }}>
              <div className="profile-avatar-wrap">
                {user.facePhoto ? (
                  <img src={user.facePhoto} alt="Face" className="profile-face-img" />
                ) : (
                  <div className="profile-avatar-initials">
                    <span>{initials}</span>
                  </div>
                )}
                <div className={`avatar-ring ${user.facePhoto ? 'ring-active' : ''}`} />
                {user.facePhoto && (
                  <div className="verified-badge" title="Face Verified">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                      <polyline points="20 6 9 17 4 12" />
                    </svg>
                  </div>
                )}
              </div>

              <div className="profile-info">
                <h1 className="profile-name">{user.name}</h1>
                <p className="profile-role">{user.role}</p>
                <p className="profile-dept">{user.department}</p>
              </div>
            </section>

            <div className="stats-row animate-fadeUp" style={{ animationDelay: '0.1s' }}>
              <div className="stat-card glass">
                <span className="stat-label">Employee ID</span>
                <span className="stat-value">{user.employeeId}</span>
              </div>
              <div className="stat-card glass">
                <span className="stat-label">Joined</span>
                <span className="stat-value">{user.joined}</span>
              </div>
              <div className="stat-card glass">
                <span className="stat-label">Access Level</span>
                <span className="stat-value">{user.accessLevel}</span>
              </div>
            </div>

            <section className="face-card glass animate-fadeUp" style={{ animationDelay: '0.15s' }}>
              <div className="face-card-header">
                <div className="face-card-title">
                  <div className="face-icon-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                      <circle cx="12" cy="8" r="4" />
                      <path d="M20 21a8 8 0 10-16 0" />
                      <path d="M9 3.5C9.5 2.5 10.5 2 12 2s2.5.5 3 1.5" strokeOpacity="0" />
                    </svg>
                  </div>
                  <span>Face Recognition</span>
                </div>
                {user.faceId && (
                  <span className="face-badge-verified">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                      <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Enrolled
                  </span>
                )}
              </div>

              <div className="face-id-display">
                {user.faceId ? (
                  <>
                    <div className="face-id-label">Recognition ID</div>
                    <div className="face-id-value gradient-text">{user.faceId}</div>
                    <div className="face-id-meta">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                      </svg>
                      Enrolled {user.enrolledAt}
                    </div>
                  </>
                ) : (
                  <div className="face-id-empty">
                    <div className="empty-face-icon">
                      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                        <circle cx="12" cy="13" r="3" />
                      </svg>
                    </div>
                    <p className="empty-face-title">No face data enrolled</p>
                    <p className="empty-face-sub">Take a photo to enroll this user on every active managed device.</p>
                  </div>
                )}
              </div>

              <div className="device-sync-card">
                <div className="device-sync-header">
                  <h3>Active Device Sync</h3>
                  <span>{activeDevices.length} device{activeDevices.length === 1 ? '' : 's'}</span>
                </div>
                {activeDevices.length === 0 ? (
                  <p className="device-sync-empty">No active devices are configured in the admin panel yet.</p>
                ) : (
                  <div className="device-sync-list">
                    {activeDevices.map((device) => {
                      const sync = user.deviceSyncs.find((item) => item.deviceId === device.id)
                      const syncState = sync?.faceStatus || sync?.syncStatus || 'pending'
                      const syncColor = SYNC_STATUS_CONFIG[syncState] || 'amber'

                      return (
                        <div className="device-sync-item" key={device.id}>
                          <div>
                            <p className="device-sync-name">{device.name}</p>
                            <p className="device-sync-meta">{device.deviceKey}</p>
                          </div>
                          <div className="device-sync-badges">
                            <span className={`mini-pill mini-pill-${device.isOnline ? 'green' : 'amber'}`}>
                              {device.isOnline ? 'Online' : 'Waiting'}
                            </span>
                            <span className={`mini-pill mini-pill-${syncColor}`}>
                              {syncState}
                            </span>
                          </div>
                        </div>
                      )
                    })}
                  </div>
                )}
              </div>

              <button
                id="enroll-face-btn"
                className="enroll-btn"
                onClick={onOpenCamera}
                disabled={loading || refreshing || activeDevices.length === 0}
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" />
                  <circle cx="12" cy="13" r="4" />
                </svg>
                {user.faceId ? 'Re-enroll Face' : 'Enroll Now'}
              </button>
            </section>

            <section className="activity-section animate-fadeUp" style={{ animationDelay: '0.2s' }}>
              <h2 className="section-title">Recent Activity</h2>
              {user.activity.length === 0 ? (
                <div className="activity-empty glass">
                  <p>No enrollment or access activity has been reported for this user yet.</p>
                </div>
              ) : (
                <div className="activity-list glass">
                  {user.activity.map((item, index) => (
                    <div className="activity-item" key={`${item.label}-${index}`}>
                      <div className={`activity-dot activity-dot-${item.type}`} />
                      <div className="activity-content">
                        <p className="activity-label">{item.label}</p>
                        <p className="activity-time">{formatActivityTime(item.time)}</p>
                      </div>
                      <div className={`activity-tag tag-${item.type}`}>{item.tag}</div>
                    </div>
                  ))}
                </div>
              )}
            </section>
          </>
        )}
      </main>
    </div>
  )
}
