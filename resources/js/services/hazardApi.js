import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_BACKEND_API_URL,
});

export async function fetchHazardData(lon, lat) {
  const response = await api.get('/hazard', {
    params: { lon, lat },
  });
  return response.data; // { success: true, data: [...] }
}