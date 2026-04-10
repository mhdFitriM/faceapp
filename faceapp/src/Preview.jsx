import './Preview.css'

export default function Preview({ photo, onSave, onRetake, saving }) {
  return (
    <div className="preview-overlay animate-slideUp">
      <div className="preview-sheet glass">

        {/* Handle */}
        <div className="sheet-handle" />

        {/* Title */}
        <div className="preview-header">
          <h2 className="preview-title">Photo Preview</h2>
          <p className="preview-sub">Confirm your face photo before enrolling</p>
        </div>

        {/* Photo */}
        <div className="preview-img-wrap">
          <img src={photo} alt="Captured face" className="preview-img" />
          <div className="preview-img-frame" />
          {saving && (
            <div className="preview-saving-overlay animate-fadeIn">
              <div className="saving-spinner" />
              <p>Enrolling…</p>
            </div>
          )}
        </div>

        {/* Quality hints */}
        <ul className="quality-hints">
          <li className="hint-item hint-ok">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Face clearly visible
          </li>
          <li className="hint-item hint-ok">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Good lighting
          </li>
          <li className="hint-item hint-ok">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Looking at camera
          </li>
        </ul>

        {/* Actions */}
        <div className="preview-actions">
          <button
            id="retake-btn"
            className="retake-btn"
            onClick={onRetake}
            disabled={saving}
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <polyline points="1 4 1 10 7 10" />
              <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
            </svg>
            Retake
          </button>
          <button
            id="save-face-btn"
            className="save-btn"
            onClick={onSave}
            disabled={saving}
          >
            {saving ? (
              <>
                <div className="btn-spinner" />
                Enrolling…
              </>
            ) : (
              <>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                  <polyline points="17 21 17 13 7 13 7 21" />
                  <polyline points="7 3 7 8 15 8" />
                </svg>
                Save & Enroll
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  )
}
