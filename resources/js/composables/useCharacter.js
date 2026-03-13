
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const character = ref(null)

const getRouteCharacterId = () => {
  const id = route.params.characterId
  if (Array.isArray(id)) return id[0] || null
  return id ?? null
}

const fetchCharacter = async (forceId = null) => {
  try {
    loading.value = true

    let id = forceId || getRouteCharacterId()

    if (!id) {
      const listResponse = await fetch('/api/characters', {
        headers: { 'Accept': 'application/json' }
      })

      if (!listResponse.ok) throw new Error('Failed to fetch characters list')

      const characters = await listResponse.json()
      if (!Array.isArray(characters) || characters.length === 0) {
        throw new Error('No characters found. Please create one first.')
      }

      id = characters[characters.length - 1]?.id
    }

    const response = await fetch(`/api/characters/${id}`, {
      headers: { 'Accept': 'application/json' }
    })

    if (!response.ok) throw new Error('Failed to fetch character')

    const data = await response.json()
    character.value = data
    return data

  } catch (error) {
    console.error('fetchCharacter error:', error)
    character.value = null
    throw error
  } finally {
    loading.value = false
  }
}

export function useCharacter() {
  return {
    character,
    loading,
    fetchCharacter,
    getRouteCharacterId
  }
}
