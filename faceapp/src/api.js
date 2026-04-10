const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')

function buildApiUrl(path) {
  return `${apiBaseUrl}${path}`
}

async function readJson(response) {
  return response.json().catch(() => ({}))
}

async function expectJson(response, fallbackMessage) {
  const data = await readJson(response)

  if (!response.ok || data.ok === false) {
    throw new Error(data.error || data.message || fallbackMessage)
  }

  return data
}

export async function fetchAppDashboard(managedUserId) {
  const search = new URLSearchParams()

  if (managedUserId) {
    search.set('managed_user_id', managedUserId)
  }

  const path = search.size > 0
    ? `/api/app/dashboard?${search.toString()}`
    : '/api/app/dashboard'

  const response = await fetch(buildApiUrl(path), {
    headers: {
      'Accept': 'application/json',
    },
  })

  return expectJson(response, 'Failed to load the FaceApp dashboard.')
}

export async function enrollFace(payload) {
  const response = await fetch(buildApiUrl('/api/enrollments'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  return expectJson(response, 'Face enrollment failed.')
}
